<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Http;
use App\Lib\View;
use App\Models\Bem;
use App\Models\Local;

final class BensController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('bens:ver');

        $filters = [
            'setor' => trim((string) ($_GET['setor'] ?? '')),
            'centro_custo' => trim((string) ($_GET['centro_custo'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];

        View::render($this->config, 'bens/index', [
            'title' => 'Bens',
            'bens' => Bem::list($this->pdo, $filters),
            'filters' => $filters,
        ]);
    }

    public function createForm(): void
    {
        Auth::requireCapability('bens:criar');
        View::render($this->config, 'bens/form', [
            'title' => 'Novo bem',
            'bem' => null,
            'action' => '/bens/novo',
            'locais' => Local::listActiveForSelect($this->pdo),
        ]);
    }

    public function create(): void
    {
        Auth::requireCapability('bens:criar');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $data = $this->readBemFromRequest(null);
        $id = Bem::create($this->pdo, $data);
        $this->criarMovimentacao($id, 'cadastro', null, null);

        Flash::add('success', 'Bem cadastrado.');
        Http::redirect('/bens/ver?id=' . $id);
    }

    public function view(): void
    {
        Auth::requireCapability('bens:ver');
        $id = (int) ($_GET['id'] ?? 0);
        $bem = $id > 0 ? Bem::find($this->pdo, $id) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }

        $movs = $this->pdo->prepare('SELECT m.*, u.nome AS usuario_nome FROM movimentacoes m LEFT JOIN usuarios u ON u.id = m.usuario_id WHERE m.bem_id = :id ORDER BY m.id DESC');
        $movs->execute(['id' => $id]);
        $movimentacoes = $movs->fetchAll();

        $emp = $this->pdo->prepare('SELECT e.*, l.nome AS destino_local_nome, l.tipo AS destino_local_tipo FROM emprestimos e LEFT JOIN locais l ON l.id = e.destino_local_id WHERE e.bem_id = :id ORDER BY e.id DESC LIMIT 1');
        $emp->execute(['id' => $id]);
        $emprestimoAtual = $emp->fetch() ?: null;

        View::render($this->config, 'bens/view', [
            'title' => 'Bem #' . (string) $bem['tombamento'],
            'bem' => $bem,
            'movimentacoes' => $movimentacoes,
            'emprestimoAtual' => $emprestimoAtual,
            'locais' => Local::listActiveForSelect($this->pdo),
        ]);
    }

    public function etiqueta(): void
    {
        Auth::requireCapability('bens:ver');
        $id = (int) ($_GET['id'] ?? 0);
        $bem = $id > 0 ? Bem::find($this->pdo, $id) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }

        View::render($this->config, 'bens/label', [
            'title' => 'Etiqueta do bem',
            'bem' => $bem,
        ]);
    }

    public function editForm(): void
    {
        Auth::requireCapability('bens:editar');
        $id = (int) ($_GET['id'] ?? 0);
        $bem = $id > 0 ? Bem::find($this->pdo, $id) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }
        View::render($this->config, 'bens/form', [
            'title' => 'Editar bem',
            'bem' => $bem,
            'action' => '/bens/editar',
            'locais' => Local::listActiveForSelect($this->pdo),
        ]);
    }

    public function update(): void
    {
        Auth::requireCapability('bens:editar');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $bem = $id > 0 ? Bem::find($this->pdo, $id) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }

        $data = $this->readBemFromRequest($bem);
        Bem::update($this->pdo, $id, $data);

        $this->registrarMovimentacoesAutomaticas($bem, $data);

        Flash::add('success', 'Bem atualizado.');
        Http::redirect('/bens/ver?id=' . $id);
    }

    public function registrarMovimentacao(): void
    {
        Auth::requireCapability('movimentacoes:criar');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $bemId = (int) ($_POST['bem_id'] ?? 0);
        $bem = $bemId > 0 ? Bem::find($this->pdo, $bemId) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }

        $tipo = trim((string) ($_POST['tipo'] ?? ''));
        $observacao = trim((string) ($_POST['observacao'] ?? ''));
        if ($tipo === '') {
            Flash::add('danger', 'Informe o tipo.');
            Http::redirect('/bens/ver?id=' . $bemId);
        }

        $this->criarMovimentacao($bemId, $tipo, $observacao === '' ? null : $observacao, null);
        Flash::add('success', 'Movimentação registrada.');
        Http::redirect('/bens/ver?id=' . $bemId);
    }

    public function emprestar(): void
    {
        Auth::requireCapability('emprestimos:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $bemId = (int) ($_POST['bem_id'] ?? 0);
        $bem = $bemId > 0 ? Bem::find($this->pdo, $bemId) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }
        if ((string) $bem['status'] !== 'ativo') {
            Flash::add('danger', 'Somente bens ativos podem ser emprestados.');
            Http::redirect('/bens/ver?id=' . $bemId);
        }

        $dataPrevista = trim((string) ($_POST['data_prevista'] ?? ''));
        $observacao = trim((string) ($_POST['observacao'] ?? ''));
        $retiradoNome = trim((string) ($_POST['retirado_por_nome'] ?? ''));
        $retiradoDoc = trim((string) ($_POST['retirado_por_documento'] ?? ''));
        $retiradoContato = trim((string) ($_POST['retirado_por_contato'] ?? ''));
        $destinoLocalId = (int) ($_POST['destino_local_id'] ?? 0);
        $destinoEndereco = trim((string) ($_POST['destino_endereco'] ?? ''));

        $user = Auth::user();
        $usuarioId = $user ? (int) $user['id'] : null;
        $agora = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO emprestimos (bem_id, usuario_id, retirado_por_nome, retirado_por_documento, retirado_por_contato, destino_local_id, destino_endereco, data_saida, data_prevista, status, observacao)
             VALUES (:bem_id, :usuario_id, :retirado_por_nome, :retirado_por_documento, :retirado_por_contato, :destino_local_id, :destino_endereco, :data_saida, :data_prevista, :status, :observacao)'
        );
        $stmt->execute([
            'bem_id' => $bemId,
            'usuario_id' => $usuarioId,
            'retirado_por_nome' => $retiradoNome === '' ? null : $retiradoNome,
            'retirado_por_documento' => $retiradoDoc === '' ? null : $retiradoDoc,
            'retirado_por_contato' => $retiradoContato === '' ? null : $retiradoContato,
            'destino_local_id' => $destinoLocalId > 0 ? $destinoLocalId : null,
            'destino_endereco' => $destinoEndereco === '' ? null : $destinoEndereco,
            'data_saida' => $agora,
            'data_prevista' => $dataPrevista === '' ? null : $dataPrevista,
            'status' => 'aberto',
            'observacao' => $observacao === '' ? null : $observacao,
        ]);

        $upd = $this->pdo->prepare("UPDATE bens SET status = 'emprestado' WHERE id = :id");
        $upd->execute(['id' => $bemId]);

        $this->criarMovimentacao($bemId, 'emprestimo', $observacao === '' ? null : $observacao, [
            'data_prevista' => $dataPrevista === '' ? null : $dataPrevista,
            'retirado_por_nome' => $retiradoNome === '' ? null : $retiradoNome,
            'retirado_por_documento' => $retiradoDoc === '' ? null : $retiradoDoc,
            'retirado_por_contato' => $retiradoContato === '' ? null : $retiradoContato,
            'destino_local_id' => $destinoLocalId > 0 ? $destinoLocalId : null,
            'destino_endereco' => $destinoEndereco === '' ? null : $destinoEndereco,
        ]);

        Flash::add('success', 'Empréstimo registrado.');
        Http::redirect('/bens/ver?id=' . $bemId);
    }

    public function devolver(): void
    {
        Auth::requireCapability('emprestimos:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $bemId = (int) ($_POST['bem_id'] ?? 0);
        $bem = $bemId > 0 ? Bem::find($this->pdo, $bemId) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }
        if ((string) $bem['status'] !== 'emprestado') {
            Flash::add('danger', 'Este bem não está emprestado.');
            Http::redirect('/bens/ver?id=' . $bemId);
        }

        $observacao = trim((string) ($_POST['observacao'] ?? ''));
        $agora = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $emp = $this->pdo->prepare("SELECT * FROM emprestimos WHERE bem_id = :id AND status = 'aberto' ORDER BY id DESC LIMIT 1");
        $emp->execute(['id' => $bemId]);
        $emprestimo = $emp->fetch();
        if (!$emprestimo) {
            Flash::add('danger', 'Empréstimo em aberto não encontrado.');
            Http::redirect('/bens/ver?id=' . $bemId);
        }

        $updEmp = $this->pdo->prepare("UPDATE emprestimos SET status = 'devolvido', data_devolucao = :data_devolucao WHERE id = :id");
        $updEmp->execute(['id' => (int) $emprestimo['id'], 'data_devolucao' => $agora]);

        $updBem = $this->pdo->prepare("UPDATE bens SET status = 'ativo' WHERE id = :id");
        $updBem->execute(['id' => $bemId]);

        $this->criarMovimentacao($bemId, 'devolucao', $observacao === '' ? null : $observacao, null);

        Flash::add('success', 'Devolução registrada.');
        Http::redirect('/bens/ver?id=' . $bemId);
    }

    public function baixar(): void
    {
        Auth::requireCapability('movimentacoes:criar');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $bemId = (int) ($_POST['bem_id'] ?? 0);
        $bem = $bemId > 0 ? Bem::find($this->pdo, $bemId) : null;
        if ($bem === null) {
            Flash::add('danger', 'Bem não encontrado.');
            Http::redirect('/bens');
        }
        if ((string) $bem['status'] === 'baixado') {
            Flash::add('danger', 'Este bem já está baixado.');
            Http::redirect('/bens/ver?id=' . $bemId);
        }

        $observacao = trim((string) ($_POST['observacao'] ?? ''));
        $updBem = $this->pdo->prepare("UPDATE bens SET status = 'baixado' WHERE id = :id");
        $updBem->execute(['id' => $bemId]);

        $this->criarMovimentacao($bemId, 'baixa', $observacao === '' ? null : $observacao, null);

        Flash::add('success', 'Baixa registrada.');
        Http::redirect('/bens/ver?id=' . $bemId);
    }

    private function readBemFromRequest(?array $bemAtual): array
    {
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        if ($descricao === '') {
            Flash::add('danger', 'Descrição é obrigatória.');
            Http::redirect($bemAtual ? '/bens/editar?id=' . (int) $bemAtual['id'] : '/bens/novo');
        }

        $fotoUrl = $bemAtual['foto_url'] ?? null;
        if (isset($_FILES['foto']) && is_array($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $fotoUrl = $this->handleUpload($_FILES['foto']);
        }

        $valor = trim((string) ($_POST['valor_aquisicao'] ?? ''));
        $valorAquisicao = $valor === '' ? null : (float) str_replace(',', '.', $valor);

        $vida = trim((string) ($_POST['vida_util'] ?? ''));
        $vidaUtil = $vida === '' ? null : (int) $vida;

        $dataAq = trim((string) ($_POST['data_aquisicao'] ?? ''));
        $dataAquisicao = $dataAq === '' ? null : $dataAq;

        $status = (string) ($_POST['status'] ?? 'ativo');
        if (!in_array($status, ['ativo', 'emprestado', 'baixado'], true)) {
            $status = 'ativo';
        }

        return [
            'codigo_interno' => trim((string) ($_POST['codigo_interno'] ?? '')) ?: null,
            'descricao' => $descricao,
            'categoria' => trim((string) ($_POST['categoria'] ?? '')) ?: null,
            'marca' => trim((string) ($_POST['marca'] ?? '')) ?: null,
            'modelo' => trim((string) ($_POST['modelo'] ?? '')) ?: null,
            'numero_serie' => trim((string) ($_POST['numero_serie'] ?? '')) ?: null,
            'valor_aquisicao' => $valorAquisicao,
            'data_aquisicao' => $dataAquisicao,
            'vida_util' => $vidaUtil,
            'centro_custo' => trim((string) ($_POST['centro_custo'] ?? '')) ?: null,
            'setor' => trim((string) ($_POST['setor'] ?? '')) ?: null,
            'localizacao' => trim((string) ($_POST['localizacao'] ?? '')) ?: null,
            'responsavel' => trim((string) ($_POST['responsavel'] ?? '')) ?: null,
            'local_id' => (int) ($_POST['local_id'] ?? 0) > 0 ? (int) $_POST['local_id'] : null,
            'status' => $status,
            'foto_url' => $fotoUrl,
            'observacoes' => trim((string) ($_POST['observacoes'] ?? '')) ?: null,
        ];
    }

    private function handleUpload(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_OK);
        if ($error !== UPLOAD_ERR_OK) {
            Flash::add('danger', $this->uploadErrorMessage($error));
            Http::redirect('/bens');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            Flash::add('danger', 'Arquivo inválido.');
            Http::redirect('/bens');
        }

        $mime = $this->detectMime($tmp, $file);

        $ext = match ($mime) {
            'image/jpg' => 'jpg',
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'image/heic-sequence' => 'heic',
            'image/heif-sequence' => 'heif',
            'image/avif' => 'avif',
            default => '',
        };

        if ($ext === '') {
            $name = (string) ($file['name'] ?? '');
            $fromName = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($fromName, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif', 'avif'], true)) {
                $ext = $fromName === 'jpeg' ? 'jpg' : $fromName;
            }
        }

        if ($ext === '') {
            Flash::add('danger', 'Formato de imagem não suportado. Use JPG/PNG/WebP/GIF (ou HEIC/HEIF).');
            Http::redirect('/bens');
        }

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir = (string) $this->config['upload_dir'];
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }
        if (!is_writable($destDir)) {
            @chmod($destDir, 0775);
        }
        if (!is_writable($destDir)) {
            Flash::add('danger', 'Pasta de upload sem permissão de escrita: ' . $destDir);
            Http::redirect('/bens');
        }
        $dest = rtrim($destDir, '/') . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            Flash::add('danger', 'Não foi possível salvar a foto. Verifique permissões da pasta uploads.');
            Http::redirect('/bens');
        }

        return rtrim((string) $this->config['upload_base'], '/') . '/' . $name;
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor).',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário).',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Servidor sem pasta temporária.',
            UPLOAD_ERR_CANT_WRITE => 'Servidor não conseguiu gravar o arquivo.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão do PHP.',
            default => 'Falha no upload da foto.',
        };
    }

    private function detectMime(string $tmp, array $file): string
    {
        $mime = '';
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = (string) $finfo->file($tmp);
        } elseif (function_exists('mime_content_type')) {
            $mime = (string) mime_content_type($tmp);
        }

        if ($mime === '') {
            $mime = (string) ($file['type'] ?? '');
        }

        $mime = strtolower(trim($mime));
        if (str_contains($mime, ';')) {
            $mime = trim(explode(';', $mime, 2)[0]);
        }

        return $mime;
    }

    private function registrarMovimentacoesAutomaticas(array $bemAntes, array $bemDepois): void
    {
        $user = Auth::user();
        $usuarioId = $user ? (int) $user['id'] : null;
        $bemId = (int) $bemAntes['id'];
        $agora = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $events = [];

        if ((string) ($bemAntes['setor'] ?? '') !== (string) ($bemDepois['setor'] ?? '')) {
            $events[] = ['tipo' => 'transferencia_setor', 'dados' => ['de' => $bemAntes['setor'] ?? null, 'para' => $bemDepois['setor'] ?? null]];
        }
        if ((string) ($bemAntes['responsavel'] ?? '') !== (string) ($bemDepois['responsavel'] ?? '')) {
            $events[] = ['tipo' => 'mudanca_responsavel', 'dados' => ['de' => $bemAntes['responsavel'] ?? null, 'para' => $bemDepois['responsavel'] ?? null]];
        }
        if ((string) ($bemAntes['status'] ?? '') !== (string) ($bemDepois['status'] ?? '')) {
            $events[] = ['tipo' => 'mudanca_status', 'dados' => ['de' => $bemAntes['status'] ?? null, 'para' => $bemDepois['status'] ?? null]];
        }

        foreach ($events as $ev) {
            $this->criarMovimentacao($bemId, (string) $ev['tipo'], null, $ev['dados']);
        }
    }

    private function criarMovimentacao(int $bemId, string $tipo, ?string $observacao, ?array $dados): void
    {
        $user = Auth::user();
        $usuarioId = $user ? (int) $user['id'] : null;
        $agora = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO movimentacoes (bem_id, tipo, usuario_id, data, observacao, dados_json) VALUES (:bem_id, :tipo, :usuario_id, :data, :observacao, :dados_json)'
        );
        $stmt->execute([
            'bem_id' => $bemId,
            'tipo' => $tipo,
            'usuario_id' => $usuarioId,
            'data' => $agora,
            'observacao' => $observacao,
            'dados_json' => $dados === null ? null : json_encode($dados, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
