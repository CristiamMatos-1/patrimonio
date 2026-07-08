<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Empresas</h1>
    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/empresas/novo')) ?>">Nova</a>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Razão social</th>
            <th>Nome fantasia</th>
            <th>CNPJ</th>
            <th>Ativo</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($empresas ?? []) as $e): ?>
            <tr>
                <td><?= (int) $e['id'] ?></td>
                <td><?= htmlspecialchars((string) $e['razao_social']) ?></td>
                <td><?= htmlspecialchars((string) ($e['nome_fantasia'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($e['cnpj'] ?? '')) ?></td>
                <td><?= (int) ($e['ativo'] ?? 0) === 1 ? 'Sim' : 'Não' ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/empresas/editar')) ?>?id=<?= (int) $e['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

