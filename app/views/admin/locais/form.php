<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$isEdit = is_array($local);
$action = $isEdit ? Http::path('/admin/locais/editar') : Http::path('/admin/locais/novo');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0"><?= $isEdit ? 'Editar local' : 'Novo local' ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/locais')) ?>">Voltar</a>
</div>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $local['id'] ?>">
    <?php endif; ?>

    <div class="col-12 col-md-6">
        <label class="form-label">Empresa</label>
        <select class="form-select" name="empresa_id" required>
            <option value="">Selecione</option>
            <?php foreach (($empresas ?? []) as $e): ?>
                <option value="<?= (int) $e['id'] ?>" <?= (int) ($local['empresa_id'] ?? 0) === (int) $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $e['razao_social']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="tipo" required>
            <?php foreach (($tipos ?? []) as $t): ?>
                <option value="<?= htmlspecialchars((string) $t) ?>" <?= (string) ($local['tipo'] ?? '') === (string) $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $t) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Nome</label>
        <input class="form-control" name="nome" required value="<?= htmlspecialchars((string) ($local['nome'] ?? '')) ?>">
    </div>

    <div class="col-12">
        <label class="form-label">Endereço</label>
        <textarea class="form-control" name="endereco" rows="3"><?= htmlspecialchars((string) ($local['endereco'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= (int) ($local['ativo'] ?? 1) === 1 ? 'checked' : '' ?>>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
</form>

