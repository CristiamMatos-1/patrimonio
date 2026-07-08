<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Usuários</h1>
    <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/usuarios/novo')) ?>">Novo</a>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Perfil</th>
            <th>Ativo</th>
            <th>Criado</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($users ?? []) as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= htmlspecialchars((string) $u['nome']) ?></td>
                <td><?= htmlspecialchars((string) $u['email']) ?></td>
                <td><?= htmlspecialchars((string) $u['perfil']) ?></td>
                <td><?= (int) $u['ativo'] === 1 ? 'Sim' : 'Não' ?></td>
                <td><?= htmlspecialchars((string) $u['criado_em']) ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/admin/usuarios/editar')) ?>?id=<?= (int) $u['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
