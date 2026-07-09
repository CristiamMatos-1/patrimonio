<?php

declare(strict_types=1);

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }

    $serverValue = $_SERVER[$key] ?? null;
    if (is_string($serverValue) && $serverValue !== '') {
        return $serverValue;
    }

    $envValue = $_ENV[$key] ?? null;
    if (is_string($envValue) && $envValue !== '') {
        return $envValue;
    }

    return $default;
};

return [
    'app_name' => $env('APP_NAME', 'Patrim么nio'),
    'base_url' => rtrim($env('BASE_URL', ''), '/'),
    'base_path' => rtrim($env('BASE_PATH', ''), '/'),
    'session_name' => $env('SESSION_NAME', 'patrimonio_session'),
    'timezone' => $env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'upload_dir' => __DIR__ . '/../public/uploads',
    'upload_base' => '/uploads',
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'coninfom_patrimonio'),
        'user' => $env('DB_USER', 'coninfom_admin'),
        'pass' => $env('DB_PASS', 'SenhaConinfoms2026'),
        'charset' => 'utf8mb4',
    ],
    'db_master' => [
        'host' => $env('DB_MASTER_HOST', 'localhost'),
        'port' => $env('DB_MASTER_PORT', '3306'),
        'name' => $env('DB_MASTER_NAME', 'coninfom_patrimonio'),
        'user' => $env('DB_MASTER_USER', 'coninfom_admin'),
        'pass' => $env('DB_MASTER_PASS', 'SenhaConinfoms2026'),
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'install_token' => $env('INSTALL_TOKEN', ''),
        'encryption_key' => $env('ENCRYPTION_KEY', 'xK9pQ2mR5vL8zW1cN7bH4tY0jF3dG6sM'),
    ],
];
