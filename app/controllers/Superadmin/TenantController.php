<?php

namespace App\Controllers\Superadmin;

use Core\Controller;
use Core\Database;
use Core\Security;
use App\Lib\Csrf;
use App\Models\TenantModel;
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
        $this->renderForm();
    }

    public function index()
    {
        $this->renderTenantsPanel();
    }

    private function renderForm(array $extraData = []): void
    {
        $this->render('superadmin/tenant_create', array_merge([
            'title' => 'Novo Cliente - SaaS Patrimonial'
        ], $extraData));
    }

    private function renderTenantsPanel(array $extraData = []): void
    {
        $dbInstance = Database::getInstance();
        $dbInstance->switchToMaster();

        $tenantModel = new TenantModel();
        $tenants = $tenantModel->getAll();

        $this->render('superadmin/tenants_index', array_merge([
            'title' => 'Gestão de Clientes SaaS',
            'tenants' => $tenants,
        ], $extraData));
    }

    /**
     * Processa a criação do Tenant e o provisionamento do banco
     */
    public function store()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        $data = [
            'cnpj' => trim((string) ($_POST['cnpj'] ?? '')),
            'razao_social' => trim((string) ($_POST['razao_social'] ?? '')),
            'nome_fantasia' => trim((string) ($_POST['nome_fantasia'] ?? '')),
            'admin_email' => trim((string) ($_POST['admin_email'] ?? '')),
            'admin_password' => (string) ($_POST['admin_password'] ?? ''),
            'db_name' => trim((string) ($_POST['db_name'] ?? '')),
            'db_user' => trim((string) ($_POST['db_user'] ?? '')),
            'db_pass' => trim((string) ($_POST['db_pass'] ?? ''))
        ];

        $cnpj = preg_replace('/\D/', '', $data['cnpj']);
        if (strlen($cnpj) !== 14) {
            $this->renderForm(['error' => 'CNPJ inválido. Informe 14 dígitos numéricos.']);
            return;
        }

        if ($data['razao_social'] === '' || $data['nome_fantasia'] === '') {
            $this->renderForm(['error' => 'Razão social e nome fantasia são obrigatórios.']);
            return;
        }

        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $this->renderForm(['error' => 'E-mail do administrador inválido.']);
            return;
        }

        if (strlen($data['admin_password']) < 8) {
            $this->renderForm(['error' => 'A senha inicial do administrador deve ter ao menos 8 caracteres.']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['db_name']) || !preg_match('/^[a-zA-Z0-9_]+$/', $data['db_user'])) {
            $this->renderForm(['error' => 'Nome do banco e usuário do banco devem conter apenas letras, números e underscore (_).']);
            return;
        }

        if ($data['db_pass'] === '') {
            $this->renderForm(['error' => 'A senha do banco de dados é obrigatória.']);
            return;
        }

        $data['cnpj'] = $cnpj;

        try {
            $service = new TenantProvisioningService();
            $service->provision($data);

            $this->renderForm([
                'success' => 'Ambiente criado com sucesso! O banco de dados do cliente foi provisionado.'
            ]);
        } catch (Exception $e) {
            error_log('Falha ao provisionar tenant: ' . $e->getMessage());
            $this->renderForm([
                'error' => 'Erro ao provisionar ambiente. Revise os dados e tente novamente.'
            ]);
        }
    }

    public function updateStatus()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        $allowedStatus = ['active', 'suspended', 'blocked'];

        if ($tenantId <= 0 || !in_array($status, $allowedStatus, true)) {
            $this->renderTenantsPanel([
                'error' => 'Parâmetros inválidos para atualização de status.'
            ]);
            return;
        }

        try {
            $dbInstance = Database::getInstance();
            $dbInstance->switchToMaster();

            $tenantModel = new TenantModel();
            $tenant = $tenantModel->findById($tenantId);
            if (!$tenant) {
                $this->renderTenantsPanel([
                    'error' => 'Tenant não encontrado.'
                ]);
                return;
            }

            $tenantModel->updateStatus($tenantId, $status);

            $statusLabel = match ($status) {
                'active' => 'ativado',
                'suspended' => 'suspenso',
                'blocked' => 'bloqueado',
                default => 'atualizado',
            };

            $this->renderTenantsPanel([
                'success' => "Cliente {$tenant['nome_fantasia']} foi {$statusLabel} com sucesso."
            ]);
        } catch (Exception $e) {
            error_log('Falha ao atualizar status do tenant: ' . $e->getMessage());
            $this->renderTenantsPanel([
                'error' => 'Erro ao atualizar status do cliente.'
            ]);
        }
    }

    public function resetAdminPassword()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $newPassword = (string) ($_POST['new_password'] ?? '');

        if ($tenantId <= 0) {
            $this->renderTenantsPanel([
                'error' => 'Tenant inválido para redefinição de senha.'
            ]);
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->renderTenantsPanel([
                'error' => 'A nova senha deve ter no mínimo 8 caracteres.'
            ]);
            return;
        }

        try {
            $dbInstance = Database::getInstance();
            $dbInstance->switchToMaster();

            $tenantModel = new TenantModel();
            $tenant = $tenantModel->findById($tenantId);
            if (!$tenant) {
                $this->renderTenantsPanel([
                    'error' => 'Tenant não encontrado.'
                ]);
                return;
            }

            $decryptedPass = Security::decrypt((string) $tenant['db_pass']);
            if (!is_string($decryptedPass) || $decryptedPass === '') {
                throw new Exception('Falha ao descriptografar senha do banco do tenant.');
            }

            $tenantDb = $dbInstance->connectTenant(
                (string) $tenant['db_host'],
                (string) $tenant['db_name'],
                (string) $tenant['db_user'],
                $decryptedPass
            );

            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = 0;

            $stmtByCnpj = $tenantDb->prepare(
                "UPDATE users SET password_hash = :password_hash WHERE cpf = :cpf LIMIT 1"
            );
            $stmtByCnpj->execute([
                ':password_hash' => $newHash,
                ':cpf' => (string) $tenant['cnpj'],
            ]);
            $updated = $stmtByCnpj->rowCount();

            if ($updated === 0) {
                $stmtByRole = $tenantDb->prepare(
                    "UPDATE users SET password_hash = :password_hash WHERE role_id = :role_id ORDER BY id ASC LIMIT 1"
                );
                $stmtByRole->execute([
                    ':password_hash' => $newHash,
                    ':role_id' => 1,
                ]);
                $updated = $stmtByRole->rowCount();
            }

            if ($updated === 0) {
                throw new Exception('Usuário administrador do tenant não encontrado para reset de senha.');
            }

            $dbInstance->switchToMaster();

            $this->renderTenantsPanel([
                'success' => "Senha do administrador de {$tenant['nome_fantasia']} redefinida com sucesso."
            ]);
        } catch (Exception $e) {
            error_log('Falha ao resetar senha do admin do tenant: ' . $e->getMessage());
            $this->renderTenantsPanel([
                'error' => 'Erro ao redefinir senha do administrador do cliente.'
            ]);
        }
    }
}
