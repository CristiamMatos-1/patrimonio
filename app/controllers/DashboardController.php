<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        Auth::requireLogin();
        $counts = [
            'bens_total' => (int) $this->pdo->query('SELECT COUNT(*) AS c FROM bens')->fetch()['c'],
            'bens_ativos' => (int) $this->pdo->query("SELECT COUNT(*) AS c FROM bens WHERE status = 'ativo'")->fetch()['c'],
            'bens_emprestados' => (int) $this->pdo->query("SELECT COUNT(*) AS c FROM bens WHERE status = 'emprestado'")->fetch()['c'],
            'bens_baixados' => (int) $this->pdo->query("SELECT COUNT(*) AS c FROM bens WHERE status = 'baixado'")->fetch()['c'],
        ];

        $porLocal = $this->pdo->query(
            "SELECT COALESCE(l.nome, 'Sem local') AS nome, COUNT(*) AS quantidade
             FROM bens b
             LEFT JOIN locais l ON l.id = b.local_id
             WHERE b.status IN ('ativo','emprestado')
             GROUP BY l.nome
             ORDER BY quantidade DESC"
        )->fetchAll();

        $emprestimosDestino = $this->pdo->query(
            "SELECT COALESCE(l.nome, 'Sem destino') AS nome, COUNT(*) AS quantidade
             FROM emprestimos e
             LEFT JOIN locais l ON l.id = e.destino_local_id
             WHERE e.status = 'aberto'
             GROUP BY l.nome
             ORDER BY quantidade DESC"
        )->fetchAll();

        $atrasados = (int) $this->pdo->query("SELECT COUNT(*) AS c FROM emprestimos WHERE status = 'aberto' AND data_prevista IS NOT NULL AND data_prevista < CURDATE()")->fetch()['c'];

        $alertas = $this->pdo->query(
            "SELECT e.id, e.data_prevista, e.data_saida, e.retirado_por_nome, b.tombamento, b.descricao, COALESCE(l.nome, '') AS destino_local_nome
             FROM emprestimos e
             JOIN bens b ON b.id = e.bem_id
             LEFT JOIN locais l ON l.id = e.destino_local_id
             WHERE e.status = 'aberto' AND e.data_prevista IS NOT NULL AND e.data_prevista < CURDATE()
             ORDER BY e.data_prevista ASC
             LIMIT 20"
        )->fetchAll();

        View::render($this->config, 'dashboard/index', [
            'title' => 'Painel',
            'counts' => $counts,
            'porLocal' => $porLocal,
            'emprestimosDestino' => $emprestimosDestino,
            'atrasados' => $atrasados,
            'alertas' => $alertas,
        ]);
    }
}
