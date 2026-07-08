<?php

namespace App\Controllers\Tenant;

use Core\Controller;
use Core\Middleware\AuthMiddleware;
use App\Models\BranchModel;
use App\Models\UserModel;
use App\Models\AssetModel;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Bloqueia acesso não logado e reconecta ao BD do Tenant
        AuthMiddleware::handle();
    }

    public function index()
    {
        $branchModel = new BranchModel();
        $userModel = new UserModel();
        $assetModel = new AssetModel();

        $this->render('tenant/dashboard', [
            'title' => 'Dashboard - Painel do Cliente',
            'userName' => $_SESSION['user_name'],
            'branchCount' => $branchModel->countAll(),
            'userCount' => $userModel->countAll(),
            'assetCount' => $assetModel->countAll()
        ]);
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
}
