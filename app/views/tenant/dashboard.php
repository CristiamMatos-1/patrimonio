<?php ob_start(); ?>

<!-- Navbar Interna do Tenant -->
<nav class="surface mb-8 rounded-lg overflow-hidden flex flex-col md:flex-row items-center justify-between p-4">
    <div class="flex items-center space-x-4 mb-4 md:mb-0">
        <span class="font-bold text-slate-900 border-r pr-4">Dashboard</span>
        <a href="<?= APP_URL ?>/branches" class="text-sm text-gray-600 hover:text-slate-900 font-medium">Filiais</a>
        <a href="<?= APP_URL ?>/users" class="text-sm text-gray-600 hover:text-slate-900 font-medium">Usuários</a>
        <a href="<?= APP_URL ?>/assets" class="text-sm text-gray-600 hover:text-slate-900 font-medium">Patrimônios</a>
        <a href="<?= APP_URL ?>/inventory" class="text-sm text-gray-600 hover:text-slate-900 font-medium">Inventário</a>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-500">Olá, <strong><?= htmlspecialchars($userName) ?></strong></span>
        <a href="<?= APP_URL ?>/logout" class="text-sm bg-red-50 text-red-600 px-3 py-1 rounded hover:bg-red-100 transition-colors font-semibold">Sair</a>
    </div>
</nav>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Cards de Resumo -->
    <div class="surface p-6 rounded-lg text-center">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Total de Filiais</h3>
        <p class="text-3xl font-bold text-slate-900"><?= isset($branchCount) ? (int) $branchCount : 0 ?></p>
    </div>
    <div class="surface p-6 rounded-lg text-center">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Total de Usuários</h3>
        <p class="text-3xl font-bold text-slate-900"><?= isset($userCount) ? (int) $userCount : 0 ?></p>
    </div>
    <div class="surface p-6 rounded-lg text-center">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Total de Patrimônios</h3>
        <p class="text-3xl font-bold text-slate-900"><?= isset($assetCount) ? (int) $assetCount : 0 ?></p>
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
?>