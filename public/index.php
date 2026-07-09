<?php

declare(strict_types=1);

/**
 * Front controller para deploy com pasta pública separada.
 *
 * Prioriza APP_SOURCE_PATH no ambiente e, na falta dele, tenta localizar
 * automaticamente a pasta protegida do projeto em cenários comuns de cPanel.
 */
function resolveBasePath(string $publicDir): string
{
    $appFolderName = basename($publicDir) . '_app';
    $candidates = [];

    foreach (['APP_SOURCE_PATH', 'PROJECT_ROOT', 'PATRIMONIO_APP_PATH'] as $key) {
        $value = getenv($key);
        if (is_string($value) && trim($value) !== '') {
            $candidates[] = trim($value);
        }
    }

    $parentDir = dirname($publicDir);
    $homeDir = dirname($parentDir);

    $candidates[] = $publicDir;
    $candidates[] = $parentDir;
    $candidates[] = $parentDir . DIRECTORY_SEPARATOR . $appFolderName;
    $candidates[] = $homeDir . DIRECTORY_SEPARATOR . $appFolderName;

    foreach ($candidates as $candidate) {
        $normalized = rtrim(str_replace('\\', '/', $candidate), '/');
        if ($normalized === '') {
            continue;
        }

        if (is_file($normalized . '/index.php') && is_dir($normalized . '/Core') && is_dir($normalized . '/app') && is_file($normalized . '/config/app.php')) {
            return $normalized;
        }
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Não foi possível localizar a pasta protegida da aplicação.\n";
    echo "Defina APP_SOURCE_PATH apontando para o diretório que contém app/, Core/, config/ e index.php.\n";
    exit;
}

define('BASE_PATH', resolveBasePath(__DIR__));

require BASE_PATH . '/index.php';
