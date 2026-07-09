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

$encryptionKey = trim($env('ENCRYPTION_KEY', ''));
$encryptionKeyFile = trim($env('ENCRYPTION_KEY_FILE', ''));
if ($encryptionKey === '' && $encryptionKeyFile !== '' && is_file($encryptionKeyFile)) {
    $fileValue = file_get_contents($encryptionKeyFile);
    if (is_string($fileValue)) {
        $encryptionKey = trim($fileValue);
    }
}

return [
    'app_name' => $env('APP_NAME', 'Patrimônio'),
    'base_url' => rtrim($env('BASE_URL', ''), '/'),
    'base_path' => rtrim($env('BASE_PATH', ''), '/'),
    'session_name' => $env('SESSION_NAME', 'patrimonio_session'),
    'timezone' => $env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'upload_dir' => __DIR__ . '/../public/uploads',
    'upload_base' => '/uploads',
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'patrimonio'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'db_master' => [
        'host' => $env('DB_MASTER_HOST', 'localhost'),
        'port' => $env('DB_MASTER_PORT', '3306'),
        'name' => $env('DB_MASTER_NAME', 'patrimonio_master'),
        'user' => $env('DB_MASTER_USER', 'root'),
        'pass' => $env('DB_MASTER_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'install_token' => $env('INSTALL_TOKEN', ''),
        'encryption_key' => $encryptionKey,
    ],
];
