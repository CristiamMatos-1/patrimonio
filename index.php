<?php

/**
 * Ponto de entrada da aplicação (Front Controller).
 * Todas as requisições passam por aqui.
 */

// Define constantes base
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

$config = require BASE_PATH . '/config/app.php';

$encryptionKey = trim((string) ($config['security']['encryption_key'] ?? ''));
if (strlen($encryptionKey) < 32) {
    error_log('Configuração inválida: ENCRYPTION_KEY não definida ou fraca.');
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro de configuração do ambiente.';
    exit;
}

$baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
if ($baseUrl === '') {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
    $scriptDir = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
    $baseUrl = $scheme . '://' . $host . $scriptDir;
}
define('APP_URL', $baseUrl);

$timezone = (string) ($config['timezone'] ?? 'America/Sao_Paulo');
if ($timezone === '') {
    $timezone = 'America/Sao_Paulo';
}
date_default_timezone_set($timezone);

$sessionName = (string) ($config['session_name'] ?? 'patrimonio_session');
if ($sessionName !== '') {
    session_name($sessionName);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o Autoloader nativo
require_once BASE_PATH . '/Core/Autoloader.php';

// Inicializa o Autoloader
$autoloader = new \Core\Autoloader();
$autoloader->register();
$autoloader->addNamespace('Core', BASE_PATH . '/Core');
$autoloader->addNamespace('App', BASE_PATH . '/app');

// Inicializa o roteador
$router = new \Core\Router();

// Definição de rotas básicas (serão expandidas depois)
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/login', 'AuthController@loginForm');
$router->add('POST', '/login', 'AuthController@authenticate');

// Rotas do Superadmin
$router->add('GET', '/superadmin/login', 'Superadmin\AuthController@loginForm');
$router->add('POST', '/superadmin/login', 'Superadmin\AuthController@authenticate');
$router->add('GET', '/superadmin/logout', 'Superadmin\AuthController@logout');
$router->add('GET', '/superadmin/tenants', 'Superadmin\TenantController@index');
$router->add('GET', '/superadmin/tenant/create', 'Superadmin\TenantController@create');
$router->add('POST', '/superadmin/tenant', 'Superadmin\TenantController@store');
$router->add('POST', '/superadmin/tenants/status', 'Superadmin\TenantController@updateStatus');
$router->add('POST', '/superadmin/tenants/reset-admin-password', 'Superadmin\TenantController@resetAdminPassword');

// Rotas do Tenant (Requer Login)
$router->add('GET', '/dashboard', 'Tenant\DashboardController@index');
$router->add('GET', '/logout', 'Tenant\DashboardController@logout');

// Rotas de Filiais
$router->add('GET', '/branches', 'Tenant\BranchController@index');
$router->add('GET', '/branches/create', 'Tenant\BranchController@create');
$router->add('POST', '/branches', 'Tenant\BranchController@store');
$router->add('GET', '/branches/edit', 'Tenant\BranchController@edit');
$router->add('POST', '/branches/update', 'Tenant\BranchController@update');
$router->add('POST', '/branches/delete', 'Tenant\BranchController@delete');

// Rotas de Usuários
$router->add('GET', '/users', 'Tenant\UserController@index');
$router->add('GET', '/users/create', 'Tenant\UserController@create');
$router->add('POST', '/users', 'Tenant\UserController@store');
$router->add('GET', '/users/edit', 'Tenant\UserController@edit');
$router->add('POST', '/users/update', 'Tenant\UserController@update');

// Rotas de Patrimônio (Assets)
$router->add('GET', '/assets', 'Tenant\AssetController@index');
$router->add('GET', '/assets/create', 'Tenant\AssetController@create');
$router->add('POST', '/assets', 'Tenant\AssetController@store');
$router->add('GET', '/assets/view', 'Tenant\AssetController@view');
$router->add('GET', '/assets/scan', 'Tenant\AssetController@scan');
$router->add('GET', '/assets/pdf', 'Tenant\AssetController@pdf');
$router->add('GET', '/assets/edit', 'Tenant\AssetController@edit');
$router->add('POST', '/assets/update', 'Tenant\AssetController@update');
$router->add('POST', '/assets/loan', 'Tenant\AssetController@loan');

// Rotas de Inventário / Auditoria
$router->add('GET', '/inventory', 'Tenant\InventoryController@index');
$router->add('POST', '/inventory/start', 'Tenant\InventoryController@start');
$router->add('GET', '/inventory/view', 'Tenant\InventoryController@view');
$router->add('POST', '/inventory/verify', 'Tenant\InventoryController@verify');
$router->add('POST', '/inventory/finalize', 'Tenant\InventoryController@finalize');

// Despacha a requisição atual
$url = $_GET['url'] ?? '/';
$router->dispatch($_SERVER['REQUEST_METHOD'], $url);
