<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';

if (!is_array($config)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Config inválida em config/app.php';
    exit;
}

$timezone = $config['timezone'] ?? 'America/Sao_Paulo';
if (!is_string($timezone) || $timezone === '') {
    $timezone = 'America/Sao_Paulo';
}
date_default_timezone_set($timezone);

$basePath = trim((string) ($config['base_path'] ?? ''));
if ($basePath !== '') {
    if ($basePath[0] !== '/') {
        $basePath = '/' . $basePath;
    }
    $basePath = rtrim($basePath, '/');
}
$config['base_path'] = $basePath;

if ($basePath !== '' && is_string($config['upload_base'] ?? null) && str_starts_with((string) $config['upload_base'], '/')) {
    $config['upload_base'] = $basePath . (string) $config['upload_base'];
}

session_name($config['session_name']);
session_start();

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = __DIR__ . '/' . $relative . '.php';
    if (is_file($path)) {
        require $path;
        return;
    }

    $parts = explode('/', $relative);
    if (count($parts) >= 2) {
        $first = array_shift($parts);
        $alt = __DIR__ . '/' . strtolower((string) $first) . '/' . implode('/', $parts) . '.php';
        if (is_file($alt)) {
            require $alt;
        }
    }
});

App\Lib\Flash::init();
App\Lib\Http::init($config);

return $config;
