<?php

namespace Core\Middleware;

class SuperadminMiddleware
{
    /**
     * Verifica se o superadmin está logado.
     * Deve ser chamado no construtor dos controllers exclusivos do superadmin.
     */
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
            header("Location: " . APP_URL . "/superadmin/login");
            exit;
        }
    }
}
