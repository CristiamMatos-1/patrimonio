<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$config = require __DIR__ . '/../app/bootstrap.php';

use App\Lib\Db;

$token = (string) ($_GET['token'] ?? '');
$expected = (string) ($config['security']['install_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $token)) {
    http_response_code(403);
    if ($expected === '') {
        echo 'INSTALL_TOKEN não configurado no servidor.';
        exit;
    }
    echo 'Token inválido.';
    exit;
}

$mode = (string) ($_GET['mode'] ?? '');
if ($mode === 'hash') {
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'GET') {
        header('Content-Type: text/html; charset=utf-8');
        $action = htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES);
        echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Gerar senha_hash</title></head><body>';
        echo '<h1>Gerar senha_hash</h1>';
        echo '<form method="post" action="' . $action . '">';
        echo '<label>Senha</label><br>';
        echo '<input type="password" name="password" required style="min-width: 280px; padding: 8px;"><br><br>';
        echo '<button type="submit">Gerar</button>';
        echo '</form>';
        echo '<p>Depois de usar, remova install.php do servidor.</p>';
        echo '</body></html>';
        exit;
    }

    $password = (string) ($_POST['password'] ?? '');
    if ($password === '') {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Campo password vazio.\n";
        exit;
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo password_hash($password, PASSWORD_DEFAULT);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

$pdo = Db::pdo($config);

$schema = (string) file_get_contents(__DIR__ . '/../database/schema.sql');
if ($schema === '') {
    http_response_code(500);
    echo 'Schema vazio';
    exit;
}

try {
    foreach (preg_split('/;\\s*(\\r?\\n|$)/', $schema) as $stmt) {
        $sql = trim($stmt);
        if ($sql === '') {
            continue;
        }
        $pdo->exec($sql);
    }

    $email = 'admin@admin.local';
    $existing = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $existing->execute(['email' => $email]);
    $has = $existing->fetch();
    if (!$has) {
        $senha = bin2hex(random_bytes(4));
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, :perfil, 1)');
        $ins->execute([
            'nome' => 'Administrador',
            'email' => $email,
            'senha_hash' => $hash,
            'perfil' => 'administrador',
        ]);
        echo 'Instalado. Login: ' . $email . ' Senha: ' . $senha;
        exit;
    }

    echo 'Instalado. Usuário admin já existe.';
} catch (Throwable $e) {
    http_response_code(500);
    $debug = getenv('APP_DEBUG') === '1';
    echo $debug ? ('Falha ao instalar: ' . $e->getMessage()) : 'Falha ao instalar';
}
