<?php ob_start(); ?>

<?php
$isEdit = isset($user) && is_array($user);
$user = $user ?? [];
$actionUrl = $action ?? '/users';
?>

<div class="max-w-2xl mx-auto surface rounded-lg p-8">
    <h2 class="text-2xl font-bold mb-6 border-b pb-4"><?= $isEdit ? 'Editar Usuário' : 'Cadastrar Novo Usuário' ?></h2>

    <form action="<?= APP_URL ?><?= htmlspecialchars($actionUrl) ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold mb-1 text-gray-700">Nome Completo</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars((string) ($user['name'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
            <div>
                <label for="cpf" class="block text-sm font-semibold mb-1 text-gray-700">CPF (Login)</label>
                <input type="text" id="cpf" name="cpf" required value="<?= htmlspecialchars((string) ($user['cpf'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Apenas números">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="email" class="block text-sm font-semibold mb-1 text-gray-700">E-mail</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1 text-gray-700"><?= $isEdit ? 'Nova senha (opcional)' : 'Senha Inicial' ?></label>
                <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="branch_id" class="block text-sm font-semibold mb-1 text-gray-700">Vincular a qual Filial?</label>
                <select id="branch_id" name="branch_id" required class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none bg-white">
                    <option value="">Selecione...</option>
                    <?php foreach($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>" <?= (int) ($user['branch_id'] ?? 0) === (int) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="role_id" class="block text-sm font-semibold mb-1 text-gray-700">Perfil de Acesso (ACL)</label>
                <select id="role_id" name="role_id" required class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none bg-white">
                    <option value="">Selecione...</option>
                    <?php foreach($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= (int) ($user['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="status" class="block text-sm font-semibold mb-1 text-gray-700">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none bg-white">
                    <?php $status = $user['status'] ?? 'active'; ?>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>

        <div class="mt-8 pt-4 flex justify-between items-center border-t">
            <a href="<?= APP_URL ?>/users" class="text-gray-500 hover:text-gray-800 font-semibold">Cancelar</a>
            <button type="submit" class="bg-slate-900 text-white font-bold py-3 px-8 rounded hover:bg-slate-800 transition-colors">
                <?= $isEdit ? 'Atualizar Usuário' : 'Salvar Usuário' ?>
            </button>
        </div>
    </form>
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