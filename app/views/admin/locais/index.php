<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Locais</h1>
    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/locais/novo')) ?>">Novo</a>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Empresa</th>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Ativo</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($locais ?? []) as $l): ?>
            <tr>
                <td><?= (int) $l['id'] ?></td>
                <td><?= htmlspecialchars((string) ($l['empresa_razao_social'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) $l['nome']) ?></td>
                <td><?= htmlspecialchars((string) $l['tipo']) ?></td>
                <td><?= (int) ($l['ativo'] ?? 0) === 1 ? 'Sim' : 'Não' ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/locais/editar')) ?>?id=<?= (int) $l['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

