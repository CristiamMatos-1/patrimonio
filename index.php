<?php

/**
 * Ponto de entrada da aplicação (Front Controller).
 * Todas as requisições passam por aqui.
 */

// Habilita a exibição de erros temporariamente para debug da tela branca
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// Define constantes base
define('BASE_PATH', __DIR__);
define('APP_URL', 'https://coninfoms.com.br/patrimonio'); // Ajustado para o seu servidor

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
$router->add('GET', '/superadmin/tenant/create', 'Superadmin\TenantController@create');
$router->add('POST', '/superadmin/tenant', 'Superadmin\TenantController@store');

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
