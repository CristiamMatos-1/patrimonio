<?php ob_start(); ?>

<div class="max-w-md mx-auto surface rounded-lg p-8 mt-10">
    <h2 class="text-2xl font-bold mb-2 text-center">Acesse sua conta</h2>
    <p class="text-sm text-gray-500 text-center mb-6">
        CPF/CNPJ para clientes ou e-mail/CNPJ para superadmin
    </p>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm font-medium">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/login" method="POST" class="space-y-5">
        <div>
            <label for="document" class="block text-sm font-semibold mb-1 text-gray-700">Documento ou e-mail</label>
            <!-- Inputs grandes (mobile-friendly / idosos) -->
            <input type="text" id="document" name="document" required 
                   class="w-full px-4 py-3 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-colors"
                   placeholder="CPF, CNPJ ou e-mail">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold mb-1 text-gray-700">Senha</label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-4 py-3 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-colors"
                   placeholder="Sua senha de acesso">
        </div>

        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded hover:bg-slate-800 transition-colors mt-2">
            Entrar
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