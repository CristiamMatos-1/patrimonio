<?php declare(strict_types=1); ?>

<?php
use App\Lib\Auth;
use App\Lib\Http;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Bens</h1>
    <?php if (Auth::can('bens:criar')): ?>
        <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/novo')) ?>">Novo</a>
    <?php endif; ?>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-12 col-md-4">
        <input class="form-control form-control-sm" name="q" placeholder="Buscar (tombamento, descrição, série...)" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>">
    </div>
    <div class="col-6 col-md-3">
        <input class="form-control form-control-sm" name="setor" placeholder="Setor" value="<?= htmlspecialchars((string) ($filters['setor'] ?? '')) ?>">
    </div>
    <div class="col-6 col-md-3">
        <input class="form-control form-control-sm" name="centro_custo" placeholder="Centro de custo" value="<?= htmlspecialchars((string) ($filters['centro_custo'] ?? '')) ?>">
    </div>
    <div class="col-6 col-md-2">
        <select class="form-select form-select-sm" name="status">
            <?php $status = (string) ($filters['status'] ?? ''); ?>
            <option value="" <?= $status === '' ? 'selected' : '' ?>>Status</option>
            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="emprestado" <?= $status === 'emprestado' ? 'selected' : '' ?>>Emprestado</option>
            <option value="baixado" <?= $status === 'baixado' ? 'selected' : '' ?>>Baixado</option>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <button class="btn btn-outline-secondary btn-sm w-100" type="submit">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>Tombamento</th>
            <th>Descrição</th>
            <th>Local</th>
            <th>Responsável</th>
            <th>Status</th>
            <th class="text-end"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($bens ?? []) as $b): ?>
            <tr>
                <td><?= (int) ($b['tombamento'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($b['descricao'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($b['local_nome'] ?? ($b['setor'] ?? ''))) ?></td>
                <td><?= htmlspecialchars((string) ($b['responsavel'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($b['status'] ?? '')) ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/ver')) ?>?id=<?= (int) ($b['id'] ?? 0) ?>">Ver</a>
                    <?php if (Auth::can('bens:editar')): ?>
                        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/editar')) ?>?id=<?= (int) ($b['id'] ?? 0) ?>">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
