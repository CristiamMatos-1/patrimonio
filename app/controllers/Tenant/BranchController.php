<?php

namespace App\Controllers\Tenant;

use App\Lib\Csrf;
use Core\Controller;
use Core\Middleware\AuthMiddleware;
use App\Models\BranchModel;

class BranchController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index()
    {
        $branchModel = new BranchModel();
        $branches = $branchModel->getAll();

        $this->render('tenant/branches/index', [
            'title' => 'Filiais - Painel do Cliente',
            'branches' => $branches
        ]);
    }

    public function create()
    {
        $this->render('tenant/branches/create', [
            'title' => 'Nova Filial - Painel do Cliente',
            'branch' => null,
            'action' => '/branches'
        ]);
    }

    public function store()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);
        $branchModel = new BranchModel();
        $branchModel->create($_POST);
        
        $this->redirect('/branches');
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $branchModel = new BranchModel();
        $branch = $branchModel->find($id);

        if (!$branch) {
            $this->redirect('/branches');
        }

        $this->render('tenant/branches/create', [
            'title' => 'Editar Filial - Painel do Cliente',
            'branch' => $branch,
            'action' => '/branches/update'
        ]);
    }

    public function update()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);
        $id = (int) ($_POST['id'] ?? 0);
        $branchModel = new BranchModel();
        $branch = $branchModel->find($id);

        if (!$branch) {
            $this->redirect('/branches');
        }

        $branchModel->update($id, $_POST);
        $this->redirect('/branches');
    }

    public function delete()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);
        $id = (int) ($_POST['id'] ?? 0);
        $branchModel = new BranchModel();
        $branch = $branchModel->find($id);

        if ($branch) {
            $branchModel->delete($id);
        }

        $this->redirect('/branches');
    }
}
