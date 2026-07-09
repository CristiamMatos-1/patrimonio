<?php ob_start(); ?>

<div class="max-w-6xl mx-auto surface rounded-lg p-8 mt-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold">Gestão de Clientes (SaaS)</h2>
            <p class="text-sm text-gray-500">Apenas superadmin pode alterar status e senha administrativa dos clientes.</p>
        </div>
        <a href="<?= APP_URL ?>/superadmin/tenant/create" class="inline-flex items-center justify-center rounded bg-slate-900 text-white px-4 py-2 text-sm font-semibold hover:bg-slate-800 transition">
            Novo cliente
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded mb-4 text-sm font-medium border border-green-200">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded mb-4 text-sm font-medium border border-red-200">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($tenants)): ?>
        <div class="text-sm text-gray-600">Nenhum cliente cadastrado.</div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 rounded">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 border-b">Cliente</th>
                        <th class="text-left px-4 py-3 border-b">CNPJ</th>
                        <th class="text-left px-4 py-3 border-b">Banco</th>
                        <th class="text-left px-4 py-3 border-b">Status</th>
                        <th class="text-left px-4 py-3 border-b">Ações de conta</th>
                        <th class="text-left px-4 py-3 border-b">Reset senha admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                        <tr class="align-top">
                            <td class="px-4 py-3 border-b">
                                <div class="font-semibold text-slate-900"><?= htmlspecialchars((string) ($tenant['nome_fantasia'] ?: $tenant['razao_social'])) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars((string) $tenant['razao_social']) ?></div>
                            </td>
                            <td class="px-4 py-3 border-b"><?= htmlspecialchars((string) $tenant['cnpj']) ?></td>
                            <td class="px-4 py-3 border-b">
                                <div><?= htmlspecialchars((string) $tenant['db_name']) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars((string) $tenant['db_user']) ?>@<?= htmlspecialchars((string) $tenant['db_host']) ?></div>
                            </td>
                            <td class="px-4 py-3 border-b">
                                <?php
                                $status = (string) ($tenant['status'] ?? '');
                                $statusLabel = match ($status) {
                                    'active' => 'Ativo',
                                    'suspended' => 'Suspenso',
                                    'blocked' => 'Bloqueado',
                                    default => 'Desconhecido',
                                };
                                $statusColor = match ($status) {
                                    'active' => 'bg-green-100 text-green-700',
                                    'suspended' => 'bg-yellow-100 text-yellow-700',
                                    'blocked' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                ?>
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold <?= $statusColor ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b">
                                <form action="<?= APP_URL ?>/superadmin/tenants/status" method="POST" class="flex flex-wrap gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
                                    <input type="hidden" name="tenant_id" value="<?= (int) $tenant['id'] ?>">
                                    <button type="submit" name="status" value="active" class="px-3 py-1 rounded border border-green-500 text-green-700 hover:bg-green-50 font-semibold">
                                        Ativar
                                    </button>
                                    <button type="submit" name="status" value="suspended" class="px-3 py-1 rounded border border-yellow-500 text-yellow-700 hover:bg-yellow-50 font-semibold">
                                        Suspender
                                    </button>
                                    <button type="submit" name="status" value="blocked" class="px-3 py-1 rounded border border-red-500 text-red-700 hover:bg-red-50 font-semibold">
                                        Bloquear
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 border-b">
                                <form action="<?= APP_URL ?>/superadmin/tenants/reset-admin-password" method="POST" class="space-y-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
                                    <input type="hidden" name="tenant_id" value="<?= (int) $tenant['id'] ?>">
                                    <input type="password" name="new_password" required minlength="8" class="w-full px-3 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none" placeholder="Nova senha admin">
                                    <button type="submit" class="w-full bg-slate-900 text-white px-3 py-2 rounded font-semibold hover:bg-slate-800 transition">
                                        Redefinir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erro: Layout principal não encontrado.";
    echo $content;
}
?>
