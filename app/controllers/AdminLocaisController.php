<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Http;
use App\Lib\View;
use App\Models\Empresa;
use App\Models\Local;

final class AdminLocaisController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('cadastros:gerir');
        View::render($this->config, 'admin/locais/index', [
            'title' => 'Locais',
            'locais' => Local::list($this->pdo),
        ]);
    }

    public function createForm(): void
    {
        Auth::requireCapability('cadastros:gerir');
        View::render($this->config, 'admin/locais/form', [
            'title' => 'Novo local',
            'local' => null,
            'empresas' => Empresa::list($this->pdo),
            'tipos' => [Local::TIPO_SEDE, Local::TIPO_FILIAL, Local::TIPO_LOCAL_ENCONTRO],
        ]);
    }

    public function create(): void
    {
        Auth::requireCapability('cadastros:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $data = $this->readLocalFromRequest();
        $id = Local::create($this->pdo, $data);

        Flash::add('success', 'Local criado.');
        Http::redirect('/admin/locais/editar?id=' . $id);
    }

    public function editForm(): void
    {
        Auth::requireCapability('cadastros:gerir');
        $id = (int) ($_GET['id'] ?? 0);
        $local = $id > 0 ? Local::find($this->pdo, $id) : null;
        if ($local === null) {
            Flash::add('danger', 'Local não encontrado.');
            Http::redirect('/admin/locais');
        }

        View::render($this->config, 'admin/locais/form', [
            'title' => 'Editar local',
            'local' => $local,
            'empresas' => Empresa::list($this->pdo),
            'tipos' => [Local::TIPO_SEDE, Local::TIPO_FILIAL, Local::TIPO_LOCAL_ENCONTRO],
        ]);
    }

    public function update(): void
    {
        Auth::requireCapability('cadastros:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $local = $id > 0 ? Local::find($this->pdo, $id) : null;
        if ($local === null) {
            Flash::add('danger', 'Local não encontrado.');
            Http::redirect('/admin/locais');
        }

        $data = $this->readLocalFromRequest();
        Local::update($this->pdo, $id, $data);

        Flash::add('success', 'Local atualizado.');
        Http::redirect('/admin/locais/editar?id=' . $id);
    }

    private function readLocalFromRequest(): array
    {
        $empresaId = (int) ($_POST['empresa_id'] ?? 0);
        if ($empresaId <= 0) {
            Flash::add('danger', 'Selecione uma empresa.');
            Http::redirect('/admin/locais');
        }

        $nome = trim((string) ($_POST['nome'] ?? ''));
        if ($nome === '') {
            Flash::add('danger', 'Nome é obrigatório.');
            Http::redirect('/admin/locais');
        }

        $tipo = (string) ($_POST['tipo'] ?? Local::TIPO_FILIAL);
        if (!in_array($tipo, [Local::TIPO_SEDE, Local::TIPO_FILIAL, Local::TIPO_LOCAL_ENCONTRO], true)) {
            $tipo = Local::TIPO_FILIAL;
        }

        return [
            'empresa_id' => $empresaId,
            'nome' => $nome,
            'tipo' => $tipo,
            'endereco' => trim((string) ($_POST['endereco'] ?? '')) ?: null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];
    }
}

