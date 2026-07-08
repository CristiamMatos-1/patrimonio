<?php

declare(strict_types=1);

namespace App\Lib;

final class Http
{
    private static string $basePath = '';

    public static function init(array $config): void
    {
        $basePath = trim((string) ($config['base_path'] ?? ''));
        if ($basePath !== '') {
            if ($basePath[0] !== '/') {
                $basePath = '/' . $basePath;
            }
            $basePath = rtrim($basePath, '/');
        }
        self::$basePath = $basePath;
    }

    public static function redirect(string $to): never
    {
        $to = self::path($to);
        header('Location: ' . $to, true, 302);
        exit;
    }

    public static function path(string $to): string
    {
        if (self::$basePath !== '' && ($to === self::$basePath || str_starts_with($to, self::$basePath . '/'))) {
            return $to;
        }
        if (str_starts_with($to, '/') && self::$basePath !== '') {
            return self::$basePath . $to;
        }
        return $to;
    }

    public static function url(array $config, string $path): string
    {
        $base = $config['base_url'];
        if ($base === '') {
            return $path;
        }
        return $base . $path;
    }
}
