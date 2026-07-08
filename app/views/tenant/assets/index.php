<?php ob_start(); ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div class="space-y-3">
        <h2 class="text-2xl font-bold">Gestão Patrimonial</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/assets/pdf" class="inline-flex items-center justify-center rounded bg-slate-900 text-white px-4 py-2 text-sm font-semibold hover:bg-slate-800 transition">Exportar PDF</a>
            <a href="<?= APP_URL ?>/assets/scan" class="inline-flex items-center justify-center rounded bg-white border border-slate-300 text-slate-900 px-4 py-2 text-sm font-semibold hover:bg-slate-50 transition">Buscar por QR</a>
        </div>
    </div>
    <a href="<?= APP_URL ?>/assets/create" class="bg-slate-900 text-white px-4 py-2 rounded font-semibold hover:bg-slate-800 transition">Cadastrar Bem</a>
</div>

<!-- Barra de Busca Global -->
<div class="surface rounded-lg p-4 mb-6">
    <form action="<?= APP_URL ?>/assets" method="GET" class="flex gap-2">
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" 
               placeholder="Pesquisar por Nomenclatura, Número do Bem ou ler QR Code..." 
               class="flex-grow px-4 py-2 rounded border focus:border-slate-900 outline-none"
               autofocus>
        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded font-semibold hover:bg-gray-700 transition">Buscar</button>
        <?php if(!empty($search)): ?>
            <a href="<?= APP_URL ?>/assets" class="bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold hover:bg-gray-300 transition">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<div class="surface rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold text-gray-600">Patrimônio</th>
                    <th class="p-4 font-semibold text-gray-600">Número</th>
                    <th class="p-4 font-semibold text-gray-600">Filial</th>
                    <th class="p-4 font-semibold text-gray-600">Status</th>
                    <th class="p-4 font-semibold text-gray-600">Ações</th>
                    <th class="p-4 font-semibold text-center text-gray-600">QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($assets)): ?>
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Nenhum bem patrimonial encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($assets as $asset): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-medium text-slate-900">
                            <?= htmlspecialchars($asset['name']) ?>
                        </td>
                        <td class="p-4 text-gray-600 font-mono text-sm">#<?= htmlspecialchars($asset['asset_number']) ?></td>
                        <td class="p-4 text-gray-600"><?= htmlspecialchars($asset['branch_name']) ?></td>
                        <td class="p-4">
                            <?php if($asset['status'] === 'active'): ?>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Ativo</span>
                            <?php elseif($asset['status'] === 'maintenance'): ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded">Em Manutenção</span>
                            <?php elseif($asset['status'] === 'borrowed'): ?>
                                <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2 py-1 rounded">Emprestado</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Baixado</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <a class="text-blue-600 hover:underline" href="<?= APP_URL ?>/assets/view?id=<?= (int) $asset['id'] ?>">Ver</a>
                            <span class="mx-1">•</span>
                            <a class="text-slate-700 hover:underline" href="<?= APP_URL ?>/assets/edit?id=<?= (int) $asset['id'] ?>">Editar</a>
                        </td>
                        <td class="p-4 text-center">
                            <!-- Utilizamos a API do Google Charts para gerar o QR Code -->
                            <?php 
                            // Verifica se o hash existe para evitar falhas na API do Google
                            if (!empty($asset['qr_code_hash'])):
                                $qrContent = APP_URL . "/assets/view?hash=" . $asset['qr_code_hash']; 
                            ?>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($qrContent) ?>" 
                                     alt="QR Code" 
                                     class="inline-block border p-1 bg-white rounded cursor-pointer hover:scale-150 transition-transform origin-right">
                            <?php else: ?>
                                <span class="text-xs text-gray-400">Sem QR</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    <a href="<?= APP_URL ?>/dashboard" class="text-blue-600 font-semibold hover:underline">&larr; Voltar ao Dashboard</a>
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