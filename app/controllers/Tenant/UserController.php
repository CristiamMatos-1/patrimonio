<?php

namespace App\Controllers\Tenant;

use Core\Controller;
use Core\Middleware\AuthMiddleware;
use App\Lib\Csrf;
use App\Models\UserModel;
use App\Models\BranchModel;
use App\Models\RoleModel;

class UserController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->getAll();

        $this->render('tenant/users/index', [
            'title' => 'Usuários - Painel do Cliente',
            'users' => $users
        ]);
    }

    public function create()
    {
        $branchModel = new BranchModel();
        $roleModel = new RoleModel();

        $this->render('tenant/users/create', [
            'title' => 'Novo Usuário - Painel do Cliente',
            'branches' => $branchModel->getAll(),
            'roles' => $roleModel->getAll(),
            'user' => null,
            'action' => '/users'
        ]);
    }

    public function store()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);
        $userModel = new UserModel();
        $userModel->create($_POST);
        
        $this->redirect('/users');
    }

    public function edit()
    {
        $userModel = new UserModel();
        $id = (int) ($_GET['id'] ?? 0);
        $user = $userModel->findById($id);

        if (!$user) {
            $this->redirect('/users');
        }

        $branchModel = new BranchModel();
        $roleModel = new RoleModel();

        $this->render('tenant/users/create', [
            'title' => 'Editar Usuário - Painel do Cliente',
            'branches' => $branchModel->getAll(),
            'roles' => $roleModel->getAll(),
            'user' => $user,
            'action' => '/users/update'
        ]);
    }

    public function update()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);
        $userModel = new UserModel();
        $id = (int) ($_POST['id'] ?? 0);
        $user = $userModel->findById($id);

        if (!$user) {
            $this->redirect('/users');
        }

        $userModel->update($id, $_POST);
        $this->redirect('/users');
    }
}
