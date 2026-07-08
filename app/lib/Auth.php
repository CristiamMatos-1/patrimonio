<?php

declare(strict_types=1);

namespace App\Lib;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return null;
        }
        return $_SESSION['user'];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $userRow): void
    {
        $_SESSION['user'] = [
            'id' => (int) $userRow['id'],
            'nome' => (string) $userRow['nome'],
            'email' => (string) $userRow['email'],
            'perfil' => (string) $userRow['perfil'],
            'ativo' => (int) $userRow['ativo'],
        ];
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }
        Flash::add('warning', 'Faça login para continuar.');
        Http::redirect('/login');
    }

    public static function can(string $capability): bool
    {
        $user = self::user();
        if ($user === null || (int) $user['ativo'] !== 1) {
            return false;
        }

        $perfil = (string) $user['perfil'];
        if ($perfil === User::PERFIL_ADMIN) {
            return true;
        }

        if ($perfil === User::PERFIL_OPERADOR) {
            return in_array($capability, [
                'bens:ver',
                'bens:criar',
                'bens:editar',
                'cadastros:gerir',
                'movimentacoes:criar',
                'emprestimos:gerir',
                'inventario:gerir',
                'relatorios:ver',
            ], true);
        }

        if ($perfil === User::PERFIL_CONSULTA) {
            return in_array($capability, [
                'bens:ver',
                'relatorios:ver',
            ], true);
        }

        return false;
    }

    public static function requireCapability(string $capability): void
    {
        self::requireLogin();
        if (self::can($capability)) {
            return;
        }
        http_response_code(403);
        echo 'Acesso negado';
        exit;
    }
}
