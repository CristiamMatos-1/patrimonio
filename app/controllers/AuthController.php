<?php

namespace App\Controllers;

use Core\Controller;

class AuthController extends Controller
{
    public function loginForm()
    {
        $this->render('auth/login', [
            'title' => 'Login - SaaS Patrimonial'
        ]);
    }

    public function authenticate()
    {
        $identifier = trim((string) ($_POST['document'] ?? ''));
        $document = preg_replace('/[^0-9]/', '', $identifier);
        $password = $_POST['password'] ?? '';

        if (($identifier === '' && $document === '') || empty($password)) {
            return $this->render('auth/login', [
                'title' => 'Login - SaaS Patrimonial',
                'error' => 'Por favor, preencha todos os campos.'
            ]);
        }

        try {
            // 1. Garante que estamos conectados ao Banco Master
            $dbInstance = \Core\Database::getInstance();
            $dbInstance->switchToMaster();
            $pdo = $dbInstance->getConnection();

            $stmtSuper = $pdo->prepare(
                "SELECT id, email, password_hash
                 FROM super_admins
                 WHERE email = :identifier OR email = :document
                 LIMIT 1"
            );
            $stmtSuper->execute([
                ':identifier' => $identifier,
                ':document' => $document,
            ]);
            $superadmin = $stmtSuper->fetch(\PDO::FETCH_ASSOC);

            if ($superadmin && password_verify($password, $superadmin['password_hash'])) {
                $_SESSION['superadmin_logged_in'] = true;
                $_SESSION['superadmin_id'] = $superadmin['id'];
                $_SESSION['superadmin_email'] = $superadmin['email'];

                $this->redirect('/superadmin/tenants');
                return;
            }

            if ($document === '') {
                return $this->render('auth/login', [
                    'title' => 'Login - SaaS Patrimonial',
                    'error' => 'Para acesso de cliente, informe um CPF ou CNPJ válido.'
                ]);
            }

            // 2. Consulta o roteamento para Tenants/Funcionários
            $routingModel = new \App\Models\RoutingModel();
            $tenantData = $routingModel->getTenantByDocument($document);

            if (!$tenantData) {
                return $this->render('auth/login', [
                    'title' => 'Login - SaaS Patrimonial',
                    'error' => 'Acesso negado. Documento não encontrado.'
                ]);
            }

            if ($tenantData['status'] !== 'active') {
                $statusMessage = $tenantData['status'] === 'blocked'
                    ? 'Conta bloqueada pelo superadmin.'
                    : 'Conta suspensa pelo superadmin.';

                return $this->render('auth/login', [
                    'title' => 'Login - SaaS Patrimonial',
                    'error' => $statusMessage
                ]);
            }

            // 3. Descriptografa a senha do banco do cliente
            $dbPass = \Core\Security::decrypt($tenantData['db_pass']);
            if (!$dbPass) {
                throw new \Exception("Erro ao descriptografar credenciais do banco de dados.");
            }

            // 4. Realiza o Switch Dinâmico para o Banco do Tenant
            $dbInstance->connectTenant(
                $tenantData['db_host'],
                $tenantData['db_name'],
                $tenantData['db_user'],
                $dbPass
            );

            // 5. Agora instanciamos o UserModel. 
            // Como o DB interno já está no Tenant, o Model fará a query na base isolada!
            $userModel = new \App\Models\UserModel();
            $user = $userModel->getUserByDocument($document);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login com sucesso!
                $_SESSION['logged_in'] = true;
                $_SESSION['tenant_id'] = $tenantData['tenant_id'];
                $_SESSION['tenant_cnpj'] = $tenantData['cnpj'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role_id'] = $user['role_id'];

                // Registrar auditoria de login
                \Core\Services\AuditService::log('LOGIN', 'users', $user['id']);
                
                // Redireciona para o painel
                $this->redirect('/dashboard');
            } else {
                return $this->render('auth/login', [
                    'title' => 'Login - SaaS Patrimonial',
                    'error' => 'Senha incorreta.'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Erro no login de usuário: ' . $e->getMessage());
            return $this->render('auth/login', [
                'title' => 'Login - SaaS Patrimonial',
                'error' => 'Erro interno. Tente novamente.'
            ]);
        }
    }
}
