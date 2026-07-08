<?php ob_start(); ?>

<div class="max-w-2xl mx-auto surface rounded-lg p-8 mt-10">
    <h2 class="text-2xl font-bold mb-6 border-b pb-4">Provisionar Novo Cliente (Tenant)</h2>

    <?php if (!empty($success)): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded mb-6 text-sm font-medium border border-green-200">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded mb-6 text-sm font-medium border border-red-200">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/superadmin/tenant" method="POST" class="space-y-5">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="cnpj" class="block text-sm font-semibold mb-1 text-gray-700">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none"
                       placeholder="Somente números">
            </div>

            <div>
                <label for="nome_fantasia" class="block text-sm font-semibold mb-1 text-gray-700">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none">
            </div>
        </div>

        <div>
            <label for="razao_social" class="block text-sm font-semibold mb-1 text-gray-700">Razão Social</label>
            <input type="text" id="razao_social" name="razao_social" required 
                   class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none">
        </div>

        <h3 class="text-lg font-bold mt-8 mb-2 border-b pb-2">Configuração do Banco de Dados no cPanel</h3>
        <p class="text-sm text-gray-500 mb-4">Crie o banco de dados manualmente no seu cPanel antes de preencher esta seção e atribua o usuário a ele.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="db_name" class="block text-sm font-semibold mb-1 text-gray-700">Nome do Banco</label>
                <input type="text" id="db_name" name="db_name" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none"
                       placeholder="Ex: coninfom_empresa1">
            </div>

            <div>
                <label for="db_user" class="block text-sm font-semibold mb-1 text-gray-700">Usuário do Banco</label>
                <input type="text" id="db_user" name="db_user" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none"
                       placeholder="Ex: coninfom_admin">
            </div>

            <div>
                <label for="db_pass" class="block text-sm font-semibold mb-1 text-gray-700">Senha do Banco</label>
                <input type="password" id="db_pass" name="db_pass" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none">
            </div>
        </div>

        <h3 class="text-lg font-bold mt-8 mb-4 border-b pb-2">Acesso do Administrador</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="admin_email" class="block text-sm font-semibold mb-1 text-gray-700">E-mail do Administrador</label>
                <input type="email" id="admin_email" name="admin_email" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none">
            </div>

            <div>
                <label for="admin_password" class="block text-sm font-semibold mb-1 text-gray-700">Senha Inicial</label>
                <input type="password" id="admin_password" name="admin_password" required 
                       class="w-full px-4 py-2 rounded border border-gray-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none">
            </div>
        </div>

        <div class="mt-8 pt-4">
            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded hover:bg-slate-800 transition-colors">
                Criar Ambiente e Rodar Migrations
            </button>
            <p class="text-xs text-gray-500 text-center mt-3">
                Esta ação conectará ao banco de dados especificado, rodará as migrations e salvará as credenciais no Master.
            </p>
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