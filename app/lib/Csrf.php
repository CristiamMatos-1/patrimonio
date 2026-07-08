<?php

declare(strict_types=1);

namespace App\Lib;

final class Csrf
{
    public static function token(): string
    {
        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): void
    {
        $expected = $_SESSION['csrf_token'] ?? '';
        if ($expected === '' || !is_string($token) || !hash_equals($expected, $token)) {
            http_response_code(403);
            echo 'CSRF inválido';
            exit;
        }
    }
}

