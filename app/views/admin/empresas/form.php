<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$isEdit = is_array($empresa);
$action = $isEdit ? Http::path('/admin/empresas/editar') : Http::path('/admin/empresas/novo');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0"><?= $isEdit ? 'Editar empresa' : 'Nova empresa' ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/empresas')) ?>">Voltar</a>
</div>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>">
    <?php endif; ?>

    <div class="col-12">
        <label class="form-label">Razão social</label>
        <input class="form-control" name="razao_social" required value="<?= htmlspecialchars((string) ($empresa['razao_social'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Nome fantasia</label>
        <input class="form-control" name="nome_fantasia" value="<?= htmlspecialchars((string) ($empresa['nome_fantasia'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">CNPJ</label>
        <input class="form-control" name="cnpj" inputmode="numeric" value="<?= htmlspecialchars((string) ($empresa['cnpj'] ?? '')) ?>">
    </div>
    <div class="col-12">
        <label class="form-label">Endereço</label>
        <textarea class="form-control" name="endereco" rows="3"><?= htmlspecialchars((string) ($empresa['endereco'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= (int) ($empresa['ativo'] ?? 1) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
</form>

