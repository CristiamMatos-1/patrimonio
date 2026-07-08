<?php ob_start(); ?>

<div class="max-w-md mx-auto surface rounded-lg p-8 mt-10">
    <div class="flex justify-center mb-4">
        <!-- Ícone ou logo para destacar que é a área restrita -->
        <div class="bg-slate-900 p-3 rounded-full">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
    </div>
    <h2 class="text-2xl font-bold mb-2 text-center text-slate-900">Acesso Restrito</h2>
    <p class="text-sm text-gray-500 text-center mb-6">
        Painel de Gerenciamento Global (Superadmin)
    </p>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm font-medium">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/superadmin/login" method="POST" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-semibold mb-1 text-gray-700">Usuário / Email</label>
            <input type="text" id="email" name="email" required 
                   class="w-full px-4 py-3 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-colors"
                   placeholder="Identificação do superadmin">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold mb-1 text-gray-700">Senha</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-4 py-3 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-colors"
                   placeholder="Sua senha de acesso">
        </div>

        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded hover:bg-slate-800 transition-colors mt-2">
            Entrar no Painel
        </button>
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