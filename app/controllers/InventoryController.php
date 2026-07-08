<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Http;
use App\Lib\View;

final class InventoryController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('inventario:gerir');
        $stmt = $this->pdo->query('SELECT i.*, u.nome AS usuario_nome FROM inventarios i LEFT JOIN usuarios u ON u.id = i.criado_por ORDER BY i.id DESC LIMIT 50');
        $inventarios = $stmt->fetchAll();

        View::render($this->config, 'inventario/index', [
            'title' => 'Inventário',
            'inventarios' => $inventarios,
        ]);
    }

    public function iniciar(): void
    {
        Auth::requireCapability('inventario:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $setor = trim((string) ($_POST['setor'] ?? ''));
        if ($setor === '') {
            Flash::add('danger', 'Informe o setor.');
            Http::redirect('/inventario');
        }

        $user = Auth::user();
        $usuarioId = $user ? (int) $user['id'] : null;

        $stmt = $this->pdo->prepare('INSERT INTO inventarios (setor, criado_por) VALUES (:setor, :criado_por)');
        $stmt->execute(['setor' => $setor, 'criado_por' => $usuarioId]);
        $invId = (int) $this->pdo->lastInsertId();

        $insert = $this->pdo->prepare('INSERT IGNORE INTO inventario_itens (inventario_id, bem_id) SELECT :inv_id, id FROM bens WHERE setor = :setor');
        $insert->execute(['inv_id' => $invId, 'setor' => $setor]);

        Flash::add('success', 'Inventário iniciado.');
        Http::redirect('/inventario/ver?id=' . $invId);
    }

    public function ver(): void
    {
        Auth::requireCapability('inventario:gerir');

        $id = (int) ($_GET['id'] ?? 0);
        $inv = null;
        if ($id > 0) {
            $stmt = $this->pdo->prepare('SELECT * FROM inventarios WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $inv = $stmt->fetch() ?: null;
        }
        if ($inv === null) {
            Flash::add('danger', 'Inventário não encontrado.');
            Http::redirect('/inventario');
        }

        $onlyPending = (string) ($_GET['pendentes'] ?? '') === '1';

        $countTotal = $this->pdo->prepare('SELECT COUNT(*) AS c FROM inventario_itens WHERE inventario_id = :id');
        $countTotal->execute(['id' => $id]);
        $total = (int) $countTotal->fetch()['c'];

        $countDone = $this->pdo->prepare('SELECT COUNT(*) AS c FROM inventario_itens WHERE inventario_id = :id AND conferido_em IS NOT NULL');
        $countDone->execute(['id' => $id]);
        $done = (int) $countDone->fetch()['c'];

        $sql = 'SELECT ii.*, b.tombamento, b.descricao, b.localizacao, b.responsavel
                FROM inventario_itens ii
                JOIN bens b ON b.id = ii.bem_id
                WHERE ii.inventario_id = :id';
        if ($onlyPending) {
            $sql .= ' AND ii.conferido_em IS NULL';
        }
        $sql .= ' ORDER BY ii.conferido_em IS NULL DESC, b.tombamento ASC LIMIT 2000';
        $stmtItens = $this->pdo->prepare($sql);
        $stmtItens->execute(['id' => $id]);
        $itens = $stmtItens->fetchAll();

        View::render($this->config, 'inventario/view', [
            'title' => 'Inventário #' . $id,
            'inv' => $inv,
            'total' => $total,
            'done' => $done,
            'onlyPending' => $onlyPending,
            'itens' => $itens,
        ]);
    }

    public function conferir(): void
    {
        Auth::requireCapability('inventario:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $invId = (int) ($_POST['inventario_id'] ?? 0);
        $bemId = (int) ($_POST['bem_id'] ?? 0);
        $tombamento = trim((string) ($_POST['tombamento'] ?? ''));

        $invStmt = $this->pdo->prepare('SELECT * FROM inventarios WHERE id = :id LIMIT 1');
        $invStmt->execute(['id' => $invId]);
        $inv = $invStmt->fetch();
        if (!$inv) {
            Flash::add('danger', 'Inventário não encontrado.');
            Http::redirect('/inventario');
        }
        if (!empty($inv['finalizado_em'])) {
            Flash::add('danger', 'Inventário finalizado.');
            Http::redirect('/inventario/ver?id=' . $invId);
        }

        if ($bemId <= 0 && $tombamento !== '') {
            $find = $this->pdo->prepare('SELECT id FROM bens WHERE tombamento = :tombamento LIMIT 1');
            $find->execute(['tombamento' => (int) $tombamento]);
            $row = $find->fetch();
            $bemId = $row ? (int) $row['id'] : 0;
        }

        if ($bemId <= 0) {
            Flash::add('danger', 'Bem não identificado.');
            Http::redirect('/inventario/ver?id=' . $invId);
        }

        $user = Auth::user();
        $usuarioId = $user ? (int) $user['id'] : null;
        $agora = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $upsert = $this->pdo->prepare(
            'INSERT INTO inventario_itens (inventario_id, bem_id, conferido_em, conferido_por)
             VALUES (:inventario_id, :bem_id, :conferido_em, :conferido_por)
             ON DUPLICATE KEY UPDATE conferido_em = VALUES(conferido_em), conferido_por = VALUES(conferido_por)'
        );
        $upsert->execute([
            'inventario_id' => $invId,
            'bem_id' => $bemId,
            'conferido_em' => $agora,
            'conferido_por' => $usuarioId,
        ]);

        Flash::add('success', 'Conferido.');
        Http::redirect('/inventario/ver?id=' . $invId);
    }

    public function finalizar(): void
    {
        Auth::requireCapability('inventario:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $invId = (int) ($_POST['inventario_id'] ?? 0);
        $stmt = $this->pdo->prepare('UPDATE inventarios SET finalizado_em = CURRENT_TIMESTAMP WHERE id = :id AND finalizado_em IS NULL');
        $stmt->execute(['id' => $invId]);

        Flash::add('success', 'Inventário finalizado.');
        Http::redirect('/inventario/ver?id=' . $invId);
    }
}

