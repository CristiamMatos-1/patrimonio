<?php ob_start(); ?>

<?php
$isEdit = isset($asset) && is_array($asset);
$asset = $asset ?? [];
$actionUrl = $action ?? '/assets';
?>

<div class="max-w-4xl mx-auto surface rounded-lg p-8">
    <h2 class="text-2xl font-bold mb-6 border-b pb-4"><?= $isEdit ? 'Editar Bem Patrimonial' : 'Cadastrar Bem Patrimonial' ?></h2>

    <form action="<?= APP_URL ?><?= htmlspecialchars($actionUrl) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) ($asset['id'] ?? 0) ?>">
        <?php endif; ?>

        <!-- Bloco 1: Identificação -->
        <div>
            <h3 class="text-lg font-bold mb-3 text-slate-800">1. Identificação</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if ($isEdit): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Número de Tombamento</label>
                        <input type="text" class="w-full px-4 py-2 rounded border bg-slate-100" value="<?= htmlspecialchars((string) ($asset['asset_number'] ?? '')) ?>" readonly>
                    </div>
                <?php endif; ?>
                <div>
                    <label for="name" class="block text-sm font-semibold mb-1 text-gray-700">Nomenclatura / Título <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Ex: Notebook Dell Inspiron" value="<?= htmlspecialchars((string) ($asset['name'] ?? '')) ?>">
                </div>
                <div>
                    <label for="internal_code" class="block text-sm font-semibold mb-1 text-gray-700">Código Interno / Etiqueta Antiga</label>
                    <input type="text" id="internal_code" name="internal_code" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none font-mono" placeholder="Opcional" value="<?= htmlspecialchars((string) ($asset['internal_code'] ?? '')) ?>">
                </div>
                <div>
                    <label for="category" class="block text-sm font-semibold mb-1 text-gray-700">Categoria</label>
                    <input type="text" id="category" name="category" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Ex: Informática, Móveis, Veículos" value="<?= htmlspecialchars((string) ($asset['category'] ?? '')) ?>">
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold mb-1 text-gray-700">Status Atual <span class="text-red-500">*</span></label>
                    <?php $status = (string) ($asset['status'] ?? 'active'); ?>
                    <select id="status" name="status" required class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none bg-white">
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Ativo (Em uso)</option>
                        <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Emprestado</option>
                        <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Em Manutenção</option>
                        <option value="written_off" <?= $status === 'written_off' ? 'selected' : '' ?>>Baixado (Inativo)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bloco 2: Especificações Técnicas -->
        <div class="pt-4 border-t">
            <h3 class="text-lg font-bold mb-3 text-slate-800">2. Especificações Técnicas</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="brand" class="block text-sm font-semibold mb-1 text-gray-700">Marca</label>
                    <input type="text" id="brand" name="brand" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" value="<?= htmlspecialchars((string) ($asset['brand'] ?? '')) ?>">
                </div>
                <div>
                    <label for="model" class="block text-sm font-semibold mb-1 text-gray-700">Modelo</label>
                    <input type="text" id="model" name="model" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" value="<?= htmlspecialchars((string) ($asset['model'] ?? '')) ?>">
                </div>
                <div>
                    <label for="serial_number" class="block text-sm font-semibold mb-1 text-gray-700">Número de Série (SN)</label>
                    <input type="text" id="serial_number" name="serial_number" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none font-mono" value="<?= htmlspecialchars((string) ($asset['serial_number'] ?? '')) ?>">
                </div>
            </div>
            <div class="mt-4">
                <label for="description" class="block text-sm font-semibold mb-1 text-gray-700">Descrição Detalhada</label>
                <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Características físicas, cor, voltagem..."><?= htmlspecialchars((string) ($asset['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <!-- Bloco 3: Localização e Responsabilidade -->
        <div class="pt-4 border-t">
            <h3 class="text-lg font-bold mb-3 text-slate-800">3. Localização e Responsabilidade</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="branch_id" class="block text-sm font-semibold mb-1 text-gray-700">Alocado na Filial <span class="text-red-500">*</span></label>
                    <select id="branch_id" name="branch_id" required class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none bg-white">
                        <option value="">Selecione...</option>
                        <?php foreach($branches as $branch): ?>
                            <option value="<?= $branch['id'] ?>" <?= (int) ($asset['branch_id'] ?? 0) === (int) $branch['id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="sector" class="block text-sm font-semibold mb-1 text-gray-700">Setor / Departamento</label>
                    <input type="text" id="sector" name="sector" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Ex: RH, Diretoria, Almoxarifado" value="<?= htmlspecialchars((string) ($asset['sector'] ?? '')) ?>">
                </div>
                <div>
                    <label for="location" class="block text-sm font-semibold mb-1 text-gray-700">Localização Exata</label>
                    <input type="text" id="location" name="location" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Ex: Sala 02, Prateleira B" value="<?= htmlspecialchars((string) ($asset['location'] ?? '')) ?>">
                </div>
                <div>
                    <label for="current_responsible" class="block text-sm font-semibold mb-1 text-gray-700">Responsável Atual</label>
                    <input type="text" id="current_responsible" name="current_responsible" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Nome do funcionário" value="<?= htmlspecialchars((string) ($asset['current_responsible'] ?? '')) ?>">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t">
            <h3 class="text-lg font-bold mb-3 text-slate-800">4. Foto do Bem</h3>
            <?php if (!empty($asset['photo_url'])): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($asset['photo_url']) ?>" alt="Foto do bem" class="w-full max-w-xs rounded-lg border">
                </div>
            <?php endif; ?>
            <div>
                <label for="photo" class="block text-sm font-semibold mb-1 text-gray-700">Foto do Bem</label>
                <input id="photo" name="photo" type="file" accept="image/*" capture="environment" class="w-full text-sm text-gray-700 file:border-0 file:bg-slate-900 file:text-white file:px-4 file:py-2 rounded" />
                <p class="mt-2 text-xs text-gray-500">Use a câmera do celular ou envie uma imagem salva.</p>
            </div>
        </div>

        <!-- Bloco 5: Dados Financeiros e Depreciação -->
        <div class="pt-4 border-t">
            <h3 class="text-lg font-bold mb-3 text-slate-800">4. Dados Financeiros</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="acquisition_date" class="block text-sm font-semibold mb-1 text-gray-700">Data de Aquisição</label>
                    <input type="date" id="acquisition_date" name="acquisition_date" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" value="<?= htmlspecialchars((string) ($asset['acquisition_date'] ?? '')) ?>">
                </div>
                <div>
                    <label for="value" class="block text-sm font-semibold mb-1 text-gray-700">Valor de Compra (R$)</label>
                    <input type="text" id="value" name="value" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="0,00" value="<?= htmlspecialchars((string) ($asset['value'] ?? '')) ?>">
                </div>
                <div>
                    <label for="useful_life_years" class="block text-sm font-semibold mb-1 text-gray-700">Vida Útil (Anos)</label>
                    <input type="number" id="useful_life_years" name="useful_life_years" value="<?= htmlspecialchars((string) ($asset['useful_life_years'] ?? '5')) ?>" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
                </div>
                <div>
                    <label for="cost_center" class="block text-sm font-semibold mb-1 text-gray-700">Centro de Custo</label>
                    <input type="text" id="cost_center" name="cost_center" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" value="<?= htmlspecialchars((string) ($asset['cost_center'] ?? '')) ?>">
                </div>
            </div>
            <div class="mt-4">
                <label for="observations" class="block text-sm font-semibold mb-1 text-gray-700">Observações Gerais</label>
                <textarea id="observations" name="observations" rows="2" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none"><?= htmlspecialchars((string) ($asset['observations'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="mt-8 pt-4 flex justify-between items-center border-t">
            <a href="<?= APP_URL ?>/assets" class="text-gray-500 hover:text-gray-800 font-semibold">Cancelar</a>
            <button type="submit" class="bg-slate-900 text-white font-bold py-3 px-8 rounded hover:bg-slate-800 transition-colors">
                <?= $isEdit ? 'Atualizar Patrimônio' : 'Salvar Patrimônio e Gerar QR Code' ?>
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