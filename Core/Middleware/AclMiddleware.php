<?php

namespace Core\Middleware;

use Core\Database;
use PDO;

class AclMiddleware
{
    /**
     * Verifica se o usuário logado tem a permissão exigida.
     * @param string $requiredPermission Ex: "asset.create"
     */
    public static function check(string $requiredPermission): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $roleId = $_SESSION['role_id'] ?? null;
        if (!$roleId) {
            self::denyAccess();
        }

        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT permissions FROM roles WHERE id = :id");
        $stmt->execute([':id' => $roleId]);
        $role = $stmt->fetch();

        if (!$role) {
            self::denyAccess();
        }

        $permissions = json_decode($role['permissions'], true);

        // Se for array e possuir o coringa "*" (Superadmin do Tenant)
        if (is_array($permissions) && in_array('*', $permissions)) {
            return; // Acesso liberado
        }

        if (!is_array($permissions) || !in_array($requiredPermission, $permissions)) {
            self::denyAccess();
        }
    }

    private static function denyAccess(): void
    {
        http_response_code(403);
        die("403 - Acesso Negado. Você não possui permissão para executar esta ação.");
    }
}
