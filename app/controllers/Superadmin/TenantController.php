<?php

namespace App\Controllers\Superadmin;

use Core\Controller;
use App\Services\TenantProvisioningService;
use Exception;

class TenantController extends Controller
{
    public function __construct()
    {
        \Core\Middleware\SuperadminMiddleware::handle();
    }

    /**
     * Exibe o formulário de cadastro de novo Tenant
     */
    public function create()
    {
        // Nota: Na prática, verificar se o usuário logado é Superadmin
        
        $this->render('superadmin/tenant_create', [
            'title' => 'Novo Cliente - SaaS Patrimonial'
        ]);
    }

    /**
     * Processa a criação do Tenant e o provisionamento do banco
     */
    public function store()
    {
        // Nota: Adicionar validações rigorosas e CSRF
        $data = [
            'cnpj' => $_POST['cnpj'] ?? '',
            'razao_social' => $_POST['razao_social'] ?? '',
            'nome_fantasia' => $_POST['nome_fantasia'] ?? '',
            'admin_email' => $_POST['admin_email'] ?? '',
            'admin_password' => $_POST['admin_password'] ?? '',
            'db_name' => $_POST['db_name'] ?? '',
            'db_user' => $_POST['db_user'] ?? '',
            'db_pass' => $_POST['db_pass'] ?? ''
        ];

        try {
            $service = new TenantProvisioningService();
            $service->provision($data);

            $this->render('superadmin/tenant_create', [
                'title' => 'Novo Cliente - SaaS Patrimonial',
                'success' => 'Ambiente criado com sucesso! O banco de dados do cliente foi provisionado.'
            ]);
        } catch (Exception $e) {
            $this->render('superadmin/tenant_create', [
                'title' => 'Novo Cliente - SaaS Patrimonial',
                'error' => 'Erro ao provisionar ambiente: ' . $e->getMessage()
            ]);
        }
    }
}
