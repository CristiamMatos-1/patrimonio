<?php

declare(strict_types=1);

namespace App\Lib;

final class Flash
{
    public static function init(): void
    {
        $_SESSION['flash'] ??= [];
    }

    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function consume(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $messages;
    }
}

