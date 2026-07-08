<?php
/**
 * SCRIPT DE CORREÇÃO AUTOMÁTICA DE AMBIENTE CPANEL
 * Este script analisa a estrutura de pastas do projeto,
 * renomeia pastas que estão com problemas de case-sensitivity (letras maiúsculas/minúsculas)
 * e tenta carregar o arquivo problemático para testar.
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "<h1>Ferramenta de Diagnóstico e Correção (cPanel)</h1>";
echo "<pre>";

$baseDir = __DIR__;
echo "Diretório Base: " . $baseDir . "\n\n";

// 1. Verificar e corrigir pasta 'app' vs 'App'
$appDir = '';
foreach (scandir($baseDir) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (strtolower($item) === 'app') {
        $appDir = $item;
        break;
    }
}

if (!$appDir) {
    die("ERRO CRÍTICO: Pasta 'app' não encontrada na raiz do projeto.");
}

if ($appDir !== 'app') {
    echo "CORRIGINDO: Renomeando pasta '$appDir' para 'app'...\n";
    rename($baseDir . '/' . $appDir, $baseDir . '/app');
    $appDir = 'app';
} else {
    echo "OK: Pasta 'app' está com o nome correto.\n";
}

// 2. Verificar e corrigir pasta 'Controllers' vs 'controllers'
$controllersDir = '';
$appPath = $baseDir . '/' . $appDir;
foreach (scandir($appPath) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (strtolower($item) === 'controllers') {
        $controllersDir = $item;
        break;
    }
}

if (!$controllersDir) {
    die("ERRO CRÍTICO: Pasta 'Controllers' não encontrada dentro de 'app/'.");
}

if ($controllersDir !== 'Controllers') {
    echo "CORRIGINDO: Renomeando pasta 'app/$controllersDir' para 'app/Controllers'...\n";
    rename($appPath . '/' . $controllersDir, $appPath . '/Controllers');
    $controllersDir = 'Controllers';
} else {
    echo "OK: Pasta 'Controllers' está com o nome correto.\n";
}

// 3. Verificar e corrigir pasta 'Superadmin'
$superadminDir = '';
$controllersPath = $appPath . '/' . $controllersDir;
foreach (scandir($controllersPath) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (strtolower($item) === 'superadmin') {
        $superadminDir = $item;
        break;
    }
}

if (!$superadminDir) {
    echo "CRIANDO: Pasta 'Superadmin' não existia. Criando agora...\n";
    mkdir($controllersPath . '/Superadmin', 0755);
    $superadminDir = 'Superadmin';
} elseif ($superadminDir !== 'Superadmin') {
    echo "CORRIGINDO: Renomeando pasta 'app/Controllers/$superadminDir' para 'app/Controllers/Superadmin'...\n";
    rename($controllersPath . '/' . $superadminDir, $controllersPath . '/Superadmin');
    $superadminDir = 'Superadmin';
} else {
    echo "OK: Pasta 'Superadmin' está com o nome correto.\n";
}

// 4. Verificar arquivo 'TenantController.php'
$tenantFile = '';
$superadminPath = $controllersPath . '/' . $superadminDir;
foreach (scandir($superadminPath) as $item) {
    if ($item === '.' || $item === '..') continue;
    if (strtolower($item) === 'tenantcontroller.php') {
        $tenantFile = $item;
        break;
    }
}

if (!$tenantFile) {
    echo "ERRO CRÍTICO: O arquivo 'TenantController.php' não existe dentro da pasta 'app/Controllers/Superadmin/'.\n";
    echo "-> POR FAVOR, FAÇA O UPLOAD DESTE ARQUIVO PELO CPANEL AGORA.\n";
} elseif ($tenantFile !== 'TenantController.php') {
    echo "CORRIGINDO: Renomeando arquivo '$tenantFile' para 'TenantController.php'...\n";
    rename($superadminPath . '/' . $tenantFile, $superadminPath . '/TenantController.php');
    echo "-> SUCESSO! O arquivo foi renomeado.\n";
} else {
    echo "OK: Arquivo 'TenantController.php' está com o nome correto.\n";
}

echo "\n---------------------------------------------------\n";
echo "DIAGNÓSTICO CONCLUÍDO.\n";
echo "Se houveram correções acima, tente acessar a página do sistema novamente.\n";
echo "</pre>";
