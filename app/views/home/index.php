<?php ob_start(); ?>

<div class="surface rounded-lg p-8 text-center mt-10">
    <h2 class="text-2xl font-bold mb-4">Bem-vindo ao SaaS de Gestão Patrimonial</h2>
    <p class="text-gray-600 mb-8 max-w-lg mx-auto leading-relaxed">
        Arquitetura Multi-Tenant com isolamento de dados por cliente, construída com PHP 8 e focada em performance e segurança.
    </p>
    
    <a href="<?= APP_URL ?>/login" class="inline-block bg-slate-900 text-white font-semibold py-3 px-8 rounded-md hover:bg-slate-800 transition-colors">
        Acessar o Sistema
    </a>
</div>

<?php 
$content = ob_get_clean();
// Usa a variável $layoutPath injetada pelo Controller que já resolve o problema de maiúsculas/minúsculas
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erro: Layout principal não encontrado.";
    echo $content; // Exibe o conteúdo mesmo sem o layout para debug
}
?>