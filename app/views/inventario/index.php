<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Inventário</h1>
</div>

<div class="border rounded bg-white p-3 mb-3">
    <div class="fw-semibold mb-2">Iniciar inventário por setor</div>
    <form method="post" action="<?= htmlspecialchars(Http::path('/inventario/iniciar')) ?>" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="col-12 col-md-6">
            <input class="form-control" name="setor" placeholder="Setor" required>
        </div>
        <div class="col-12 col-md-3">
            <button class="btn btn-primary w-100" type="submit">Iniciar</button>
        </div>
    </form>
</div>

<h2 class="h6 mb-2">Inventários recentes</h2>
<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Setor</th>
            <th>Criado em</th>
            <th>Criado por</th>
            <th>Finalizado</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($inventarios ?? []) as $i): ?>
            <tr>
                <td><?= (int) $i['id'] ?></td>
                <td><?= htmlspecialchars((string) $i['setor']) ?></td>
                <td><?= htmlspecialchars((string) $i['criado_em']) ?></td>
                <td><?= htmlspecialchars((string) ($i['usuario_nome'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($i['finalizado_em'] ?? '')) ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/inventario/ver')) ?>?id=<?= (int) $i['id'] ?>">Abrir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
