<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Gerenciar Usuários</h2>
    <a href="<?= APP_URL ?>/users/create" class="bg-slate-900 text-white px-4 py-2 rounded font-semibold hover:bg-slate-800 transition">Novo Usuário</a>
</div>

<div class="surface rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold text-gray-600">Nome</th>
                    <th class="p-4 font-semibold text-gray-600">CPF</th>
                    <th class="p-4 font-semibold text-gray-600">Perfil (ACL)</th>
                    <th class="p-4 font-semibold text-gray-600">Filial</th>
                    <th class="p-4 font-semibold text-gray-600">Status</th>
                    <th class="p-4 font-semibold text-gray-600"></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">Nenhum usuário encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($users as $user): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-medium text-slate-900">
                            <?= htmlspecialchars($user['name']) ?><br>
                            <span class="text-xs text-gray-500"><?= htmlspecialchars($user['email']) ?></span>
                        </td>
                        <td class="p-4 text-gray-600"><?= htmlspecialchars($user['cpf']) ?></td>
                        <td class="p-4 text-gray-600 font-semibold"><?= htmlspecialchars($user['role_name']) ?></td>
                        <td class="p-4 text-gray-600"><?= htmlspecialchars($user['branch_name']) ?></td>
                        <td class="p-4">
                            <?php if($user['status'] === 'active'): ?>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Ativo</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <a href="<?= APP_URL ?>/users/edit?id=<?= (int) $user['id'] ?>" class="text-blue-600 font-semibold hover:underline">Editar</a>
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