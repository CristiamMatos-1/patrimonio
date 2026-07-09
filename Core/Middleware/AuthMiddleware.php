<?php

namespace Core\Middleware;

use Core\Database;
use App\Models\RoutingModel;
use Core\Security;
use Exception;

class AuthMiddleware
{
    /**
     * Verifica se o usuário está logado e restaura a conexão PDO para o Banco do Tenant.
     * Deve ser chamado no construtor dos controllers protegidos.
     */
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['logged_in']) || empty($_SESSION['tenant_cnpj'])) {
            header("Location: " . APP_URL . "/login");
            exit;
        }

        // Restaura a conexão com o banco de dados do Tenant
        try {
            $dbInstance = Database::getInstance();
            $dbInstance->switchToMaster();

            $routingModel = new RoutingModel();
            $tenantData = $routingModel->getTenantCredentialsByDocument($_SESSION['tenant_cnpj']);

            if (!$tenantData) {
                session_destroy();
                header("Location: " . APP_URL . "/login?error=Tenant_invalido");
                exit;
            }

            $dbPass = Security::decrypt($tenantData['db_pass']);
            
            // Refaz a conexão para o escopo desta requisição
            $dbInstance->connectTenant(
                $tenantData['db_host'],
                $tenantData['db_name'],
                $tenantData['db_user'],
                $dbPass
            );

        } catch (Exception $e) {
            error_log('Erro de autenticação/roteamento: ' . $e->getMessage());
            session_destroy();
            header("Location: " . APP_URL . "/login?error=Falha_de_autenticacao");
            exit;
        }
    }
}
