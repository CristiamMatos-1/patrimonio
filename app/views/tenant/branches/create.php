<?php ob_start(); ?>

<div class="max-w-3xl mx-auto surface rounded-lg p-8">
    <?php
    $isEdit = isset($branch) && is_array($branch);
    $branch = $branch ?? [];
    $actionUrl = $action ?? '/branches';
    ?>

    <h2 class="text-2xl font-bold mb-6 border-b pb-4"><?= $isEdit ? 'Editar Filial' : 'Cadastrar Nova Filial' ?></h2>

    <form action="<?= APP_URL ?><?= htmlspecialchars($actionUrl) ?>" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $branch['id'] ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold mb-1 text-gray-700">Nome da Filial / Congregação</label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars((string) ($branch['name'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
            <div>
                <label for="cnpj" class="block text-sm font-semibold mb-1 text-gray-700">CNPJ (Opcional)</label>
                <input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars((string) ($branch['cnpj'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" id="is_headquarters" name="is_headquarters" value="1" <?= !empty($branch['is_headquarters']) ? 'checked' : '' ?> class="h-4 w-4 rounded border-gray-300 text-slate-900 focus:ring-slate-900">
                    Matriz
                </label>
            </div>
        </div>

        <h3 class="text-lg font-bold mt-8 mb-4 border-b pb-2">Endereço (Integração ViaCEP)</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="cep" class="block text-sm font-semibold mb-1 text-gray-700">CEP</label>
                <input type="text" id="cep" name="cep" value="<?= htmlspecialchars((string) ($branch['cep'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" onblur="buscarCep(this.value)">
            </div>
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-semibold mb-1 text-gray-700">Logradouro</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars((string) ($branch['address'] ?? '')) ?>" class="w-full px-4 py-2 rounded border bg-gray-50 focus:bg-white outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div>
                <label for="number" class="block text-sm font-semibold mb-1 text-gray-700">Número</label>
                <input type="text" id="number" name="number" value="<?= htmlspecialchars((string) ($branch['number'] ?? '')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
            </div>
            <div>
                <label for="neighborhood" class="block text-sm font-semibold mb-1 text-gray-700">Bairro</label>
                <input type="text" id="neighborhood" name="neighborhood" value="<?= htmlspecialchars((string) ($branch['neighborhood'] ?? '')) ?>" class="w-full px-4 py-2 rounded border bg-gray-50 focus:bg-white outline-none">
            </div>
            <div>
                <label for="city" class="block text-sm font-semibold mb-1 text-gray-700">Cidade</label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars((string) ($branch['city'] ?? '')) ?>" class="w-full px-4 py-2 rounded border bg-gray-50 focus:bg-white outline-none">
            </div>
            <div>
                <label for="state" class="block text-sm font-semibold mb-1 text-gray-700">UF</label>
                <input type="text" id="state" name="state" value="<?= htmlspecialchars((string) ($branch['state'] ?? '')) ?>" class="w-full px-4 py-2 rounded border bg-gray-50 focus:bg-white outline-none">
            </div>
        </div>

        <div class="mt-8 pt-4 flex justify-between items-center">
            <a href="<?= APP_URL ?>/branches" class="text-gray-500 hover:text-gray-800 font-semibold">Cancelar</a>
            <button type="submit" class="bg-slate-900 text-white font-bold py-3 px-8 rounded hover:bg-slate-800 transition-colors">
                Salvar Filial
            </button>
        </div>
    </form>
</div>

<script>
function buscarCep(cep) {
    cep = cep.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('address').value = data.logradouro;
                    document.getElementById('neighborhood').value = data.bairro;
                    document.getElementById('city').value = data.localidade;
                    document.getElementById('state').value = data.uf;
                    document.getElementById('number').focus();
                }
            })
            .catch(error => console.error('Erro ao buscar CEP:', error));
    }
}
</script>

<?php 
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erro: Layout principal não encontrado.";
    echo $content;
}
?>