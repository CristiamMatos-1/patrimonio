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
        $document = preg_replace('/[^0-9]/', '', $_POST['document'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($document) || empty($password)) {
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

            // -- NOVO: Verifica se é Superadmin (Login Unificado) --
            // Usamos o 'document' pois inserimos o CPF 99999999999 no campo email da tabela super_admins
            $stmtSuper = $pdo->prepare("SELECT id, email, password_hash FROM super_admins WHERE email = :email LIMIT 1");
            $stmtSuper->execute(['email' => $document]);
            $superadmin = $stmtSuper->fetch(\PDO::FETCH_ASSOC);

            if ($superadmin && password_verify($password, $superadmin['password_hash'])) {
                $_SESSION['superadmin_logged_in'] = true;
                $_SESSION['superadmin_id'] = $superadmin['id'];
                $_SESSION['superadmin_email'] = $superadmin['email'];

                $this->redirect('/superadmin/tenant/create');
                return;
            }
            // -- FIM DA VERIFICAÇÃO SUPERADMIN --

            // 2. Consulta o roteamento para Tenants/Funcionários
            $routingModel = new \App\Models\RoutingModel();
            $tenantData = $routingModel->getTenantCredentialsByDocument($document);

            if (!$tenantData) {
                return $this->render('auth/login', [
                    'title' => 'Login - SaaS Patrimonial',
                    'error' => 'Acesso negado. Documento não encontrado ou conta suspensa.'
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
            return $this->render('auth/login', [
                'title' => 'Login - SaaS Patrimonial',
                'error' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
    }
}
