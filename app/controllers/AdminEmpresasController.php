<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Http;
use App\Lib\View;
use App\Models\Empresa;

final class AdminEmpresasController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('cadastros:gerir');
        View::render($this->config, 'admin/empresas/index', [
            'title' => 'Empresas',
            'empresas' => Empresa::list($this->pdo),
        ]);
    }

    public function createForm(): void
    {
        Auth::requireCapability('cadastros:gerir');
        View::render($this->config, 'admin/empresas/form', [
            'title' => 'Nova empresa',
            'empresa' => null,
        ]);
    }

    public function create(): void
    {
        Auth::requireCapability('cadastros:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $data = $this->readEmpresaFromRequest();
        $id = Empresa::create($this->pdo, $data);

        Flash::add('success', 'Empresa criada.');
        Http::redirect('/admin/empresas/editar?id=' . $id);
    }

    public function editForm(): void
    {
        Auth::requireCapability('cadastros:gerir');
        $id = (int) ($_GET['id'] ?? 0);
        $empresa = $id > 0 ? Empresa::find($this->pdo, $id) : null;
        if ($empresa === null) {
            Flash::add('danger', 'Empresa não encontrada.');
            Http::redirect('/admin/empresas');
        }

        View::render($this->config, 'admin/empresas/form', [
            'title' => 'Editar empresa',
            'empresa' => $empresa,
        ]);
    }

    public function update(): void
    {
        Auth::requireCapability('cadastros:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $empresa = $id > 0 ? Empresa::find($this->pdo, $id) : null;
        if ($empresa === null) {
            Flash::add('danger', 'Empresa não encontrada.');
            Http::redirect('/admin/empresas');
        }

        $data = $this->readEmpresaFromRequest();
        Empresa::update($this->pdo, $id, $data);

        Flash::add('success', 'Empresa atualizada.');
        Http::redirect('/admin/empresas/editar?id=' . $id);
    }

    private function readEmpresaFromRequest(): array
    {
        $razaoSocial = trim((string) ($_POST['razao_social'] ?? ''));
        if ($razaoSocial === '') {
            Flash::add('danger', 'Razão social é obrigatória.');
            Http::redirect('/admin/empresas');
        }

        $cnpj = preg_replace('/\\D+/', '', (string) ($_POST['cnpj'] ?? '')) ?? '';
        $cnpj = $cnpj === '' ? null : $cnpj;

        return [
            'razao_social' => $razaoSocial,
            'nome_fantasia' => trim((string) ($_POST['nome_fantasia'] ?? '')) ?: null,
            'cnpj' => $cnpj,
            'endereco' => trim((string) ($_POST['endereco'] ?? '')) ?: null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];
    }
}

