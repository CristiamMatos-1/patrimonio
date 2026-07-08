<?php

declare(strict_types=1);

use App\Lib\Auth;
use App\Lib\Http;

$appName = (string) ($config['app_name'] ?? 'Patrimônio');
$titleText = isset($title) ? (string) $title : $appName;
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titleText) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= htmlspecialchars(Http::path('/')) ?>"><?= htmlspecialchars($appName) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (Auth::check()): ?>
                    <?php if (Auth::can('bens:ver')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::path('/bens')) ?>">Bens</a></li>
                    <?php endif; ?>
                    <?php if (Auth::can('inventario:gerir')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::path('/inventario')) ?>">Inventário</a></li>
                    <?php endif; ?>
                    <?php if (Auth::can('relatorios:ver')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::path('/relatorios')) ?>">Relatórios</a></li>
                    <?php endif; ?>
                    <?php if (Auth::can('cadastros:gerir')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Cadastros</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= htmlspecialchars(Http::path('/admin/empresas')) ?>">Empresas</a></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars(Http::path('/admin/locais')) ?>">Locais</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (Auth::can('usuarios:gerir')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::path('/admin/usuarios')) ?>">Usuários</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (Auth::check()): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <?= htmlspecialchars((string) ($current_user['nome'] ?? '')) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="<?= htmlspecialchars(Http::path('/logout')) ?>" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button class="btn btn-outline-light btn-sm" type="submit">Sair</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(Http::path('/login')) ?>">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php foreach (($flash ?? []) as $m): ?>
        <div class="alert alert-<?= htmlspecialchars((string) ($m['type'] ?? 'info')) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string) ($m['message'] ?? '')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endforeach; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php require $templatePath; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
