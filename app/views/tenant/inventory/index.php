<?php ob_start(); ?>

<div class="max-w-5xl mx-auto surface rounded-lg p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold">Conferência Patrimonial</h2>
            <p class="text-sm text-gray-600">Inicie uma nova conferência de ativos e acompanhe os itens verificados em tempo real.</p>
        </div>
        <a href="<?= APP_URL ?>/dashboard" class="text-blue-600 font-semibold hover:underline">Voltar ao Dashboard</a>
    </div>

    <div class="surface rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Iniciar novo inventário</h3>
        <form action="<?= APP_URL ?>/inventory/start" method="POST" class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
            <div>
                <label for="sector" class="block text-sm font-semibold mb-1 text-gray-700">Setor / Departamento</label>
                <input id="sector" name="sector" type="text" required class="w-full px-4 py-3 rounded border focus:border-slate-900 outline-none" placeholder="Ex: Almoxarifado, TI, Financeiro">
            </div>
            <button type="submit" class="bg-slate-900 text-white px-6 py-3 rounded hover:bg-slate-800 transition">Iniciar Inventário</button>
        </form>
    </div>

    <div class="surface rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Inventários recentes</h3>
        <?php if (empty($sessions)): ?>
            <p class="text-sm text-gray-500">Nenhum inventário iniciado ainda.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 font-semibold text-gray-600">ID</th>
                            <th class="p-4 font-semibold text-gray-600">Setor</th>
                            <th class="p-4 font-semibold text-gray-600">Criado por</th>
                            <th class="p-4 font-semibold text-gray-600">Itens conferidos</th>
                            <th class="p-4 font-semibold text-gray-600">Status</th>
                            <th class="p-4 font-semibold text-gray-600">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4 font-medium">#<?= (int) $session['id'] ?></td>
                                <td class="p-4 text-gray-700"><?= htmlspecialchars($session['sector'] ?? '-') ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($session['created_by_name'] ?? '-') ?></td>
                                <td class="p-4 text-gray-600"><?= (int) ($session['checked_items'] ?? 0) ?> / <?= (int) ($session['total_items'] ?? 0) ?></td>
                                <td class="p-4">
                                    <?php if (!empty($session['finalized_at'])): ?>
                                        <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded">Finalizado</span>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded">Aberto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="<?= APP_URL ?>/inventory/view?id=<?= (int) $session['id'] ?>" class="text-blue-600 hover:underline">Abrir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erro: Layout principal não encontrado.";
    echo $content;
}
