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

$defaultPublicPath = rtrim(str_replace('\\', '/', __DIR__ . '/../public'), '/');
$publicPath = rtrim($env('APP_PUBLIC_PATH', $defaultPublicPath), '/');
$uploadDir = rtrim($env('UPLOAD_DIR', $publicPath . '/uploads'), '/');

return [
    'app_name' => $env('APP_NAME', 'Patrimônio'),
    'base_url' => rtrim($env('BASE_URL', ''), '/'),
    'base_path' => rtrim($env('APP_BASE_PATH', $env('BASE_PATH', '')), '/'),
    'session_name' => $env('SESSION_NAME', 'patrimonio_session'),
    'timezone' => $env('APP_TIMEZONE', 'America/Sao_Paulo'),
    'public_path' => $publicPath,
    'upload_dir' => $uploadDir,
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
        'encryption_key' => $encryptionKey,
    ],
];
