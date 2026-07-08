<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Gerenciar Filiais</h2>
    <a href="<?= APP_URL ?>/branches/create" class="bg-slate-900 text-white px-4 py-2 rounded font-semibold hover:bg-slate-800 transition">Nova Filial</a>
</div>

<div class="surface rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold text-gray-600">Nome</th>
                    <th class="p-4 font-semibold text-gray-600">CNPJ</th>
                    <th class="p-4 font-semibold text-gray-600">Cidade/UF</th>
                    <th class="p-4 font-semibold text-gray-600">Tipo</th>
                    <th class="p-4 font-semibold text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($branches)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">Nenhuma filial encontrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($branches as $branch): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-medium text-slate-900"><?= htmlspecialchars($branch['name']) ?></td>
                        <td class="p-4 text-gray-600"><?= htmlspecialchars($branch['cnpj'] ?: '-') ?></td>
                        <td class="p-4 text-gray-600">
                            <?= htmlspecialchars($branch['city'] ?: '-') ?>/<?= htmlspecialchars($branch['state'] ?: '-') ?>
                        </td>
                        <td class="p-4">
                            <?php if($branch['is_headquarters']): ?>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">Matriz</span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded">Filial</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <a class="text-blue-600 hover:underline" href="<?= APP_URL ?>/branches/edit?id=<?= (int) $branch['id'] ?>">Editar</a>
                            <form action="<?= APP_URL ?>/branches/delete" method="POST" class="inline-block">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $branch['id'] ?>">
                                <button type="submit" onclick="return confirm('Deseja realmente excluir esta filial?');" class="text-red-600 hover:underline">Excluir</button>
                            </form>
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