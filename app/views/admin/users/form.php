<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$isEdit = is_array($user);
$action = $isEdit ? Http::path('/admin/usuarios/editar') : Http::path('/admin/usuarios/novo');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0"><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/usuarios')) ?>">Voltar</a>
</div>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
    <?php endif; ?>

    <div class="col-12 col-md-6">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required value="<?= htmlspecialchars((string) ($user['nome'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">E-mail</label>
        <input class="form-control" name="email" type="email" required value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Perfil</label>
        <select class="form-select" name="perfil" required>
            <?php foreach (($profiles ?? []) as $p): ?>
                <option value="<?= htmlspecialchars((string) $p) ?>" <?= (string) ($user['perfil'] ?? '') === (string) $p ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $p) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12 col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= (int) ($user['ativo'] ?? 1) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>

    <?php if (!$isEdit): ?>
        <div class="col-12 col-md-4">
            <label class="form-label">Senha</label>
            <input class="form-control" name="senha" type="password" required>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
</form>

<?php if ($isEdit): ?>
    <hr class="my-4">
    <h2 class="h6 mb-3">Alterar senha</h2>
    <form method="post" action="<?= htmlspecialchars(Http::path('/admin/usuarios/senha')) ?>" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
        <div class="col-12 col-md-6">
            <label class="form-label">Nova senha</label>
            <input class="form-control" name="senha" type="password" required>
        </div>
        <div class="col-12">
            <button class="btn btn-outline-primary" type="submit">Atualizar senha</button>
        </div>
    </form>
<?php endif; ?>
