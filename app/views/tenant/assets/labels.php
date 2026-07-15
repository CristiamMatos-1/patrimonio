<?php ob_start(); ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold">Imprimir Etiquetas</h2>
        <p class="text-sm text-gray-500 mt-1">Selecione os patrimônios e imprima as etiquetas (3 cm × 3 cm)</p>
    </div>
    <a href="<?= APP_URL ?>/assets" class="text-blue-600 font-semibold hover:underline">&larr; Voltar à lista</a>
</div>

<!-- Barra de ação -->
<div class="surface rounded-lg p-4 mb-4 flex flex-wrap gap-3 items-center justify-between">
    <div class="flex items-center gap-4 flex-wrap">
        <label class="flex items-center gap-2 cursor-pointer select-none text-sm font-medium text-gray-700">
            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded">
            Selecionar todos
        </label>
        <span id="selectedCount" class="text-sm text-gray-400">0 selecionados</span>
    </div>
    <div class="flex gap-2 flex-wrap">
        <button onclick="printAll()"
                class="inline-flex items-center rounded border border-slate-900 text-slate-900 px-4 py-2 text-sm font-semibold hover:bg-slate-50 transition">
            Imprimir Todos
        </button>
        <button onclick="printSelected()"
                class="inline-flex items-center rounded bg-slate-900 text-white px-4 py-2 text-sm font-semibold hover:bg-slate-800 transition">
            🏷️&nbsp; Imprimir Selecionados
        </button>
    </div>
</div>

<!-- Filtro rápido -->
<div class="surface rounded-lg p-3 mb-4">
    <input type="text" id="filterInput"
           placeholder="Filtrar por nome ou número do patrimônio..."
           class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none text-sm">
</div>

<!-- Tabela de seleção -->
<div class="surface rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="assetsTable">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 w-10"></th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Nº Patrimônio</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Nome</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Filial</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Data Aquisição</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assets)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">Nenhum patrimônio cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assets as $asset):
                        $isPreSelected = in_array((int) $asset['id'], $preSelectedIds ?? [], true);
                        $acqDate = '';
                        if (!empty($asset['acquisition_date'])) {
                            try {
                                $acqDate = (new DateTime($asset['acquisition_date']))->format('d/m/Y');
                            } catch (Exception $e) {
                                $acqDate = (string) $asset['acquisition_date'];
                            }
                        }
                    ?>
                    <tr class="border-b hover:bg-gray-50 asset-row"
                        data-name="<?= htmlspecialchars(strtolower((string) ($asset['name'] ?? ''))) ?>"
                        data-number="<?= htmlspecialchars(strtolower((string) ($asset['asset_number'] ?? ''))) ?>">
                        <td class="p-4">
                            <input type="checkbox"
                                   class="asset-checkbox w-4 h-4 rounded cursor-pointer"
                                   value="<?= (int) $asset['id'] ?>"
                                   <?= $isPreSelected ? 'checked' : '' ?>>
                        </td>
                        <td class="p-4 font-mono text-sm font-semibold text-slate-900">
                            <?= htmlspecialchars($asset['asset_number'] ?? '') ?>
                        </td>
                        <td class="p-4 text-slate-700 text-sm font-medium">
                            <?= htmlspecialchars($asset['name'] ?? '') ?>
                        </td>
                        <td class="p-4 text-gray-500 text-sm">
                            <?= htmlspecialchars($asset['branch_name'] ?? '') ?>
                        </td>
                        <td class="p-4 text-gray-500 text-sm">
                            <?= htmlspecialchars($acqDate) ?>
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="printOne(<?= (int) $asset['id'] ?>)"
                                    class="text-blue-600 hover:underline text-sm font-medium">
                                🏷️ Etiqueta
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const appUrl = <?= json_encode(APP_URL) ?>;

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.asset-checkbox:checked')).map(cb => cb.value);
}

function getVisibleIds() {
    return Array.from(
        document.querySelectorAll('.asset-row:not([style*="display: none"]) .asset-checkbox')
    ).map(cb => cb.value);
}

function updateCount() {
    const n = getSelectedIds().length;
    document.getElementById('selectedCount').textContent =
        n + ' selecionado' + (n !== 1 ? 's' : '');
}

function printSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Selecione ao menos um patrimônio para imprimir.');
        return;
    }
    window.open(appUrl + '/assets/label-print?ids=' + ids.join(','), '_blank');
}

function printAll() {
    const ids = getVisibleIds();
    if (ids.length === 0) {
        alert('Nenhum patrimônio disponível.');
        return;
    }
    window.open(appUrl + '/assets/label-print?ids=' + ids.join(','), '_blank');
}

function printOne(id) {
    window.open(appUrl + '/assets/label-print?ids=' + id, '_blank');
}

document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.asset-row:not([style*="display: none"]) .asset-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
    updateCount();
});

document.querySelectorAll('.asset-checkbox').forEach(cb => {
    cb.addEventListener('change', updateCount);
});

document.getElementById('filterInput').addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.asset-row').forEach(row => {
        const visible = !term
            || (row.dataset.name || '').includes(term)
            || (row.dataset.number || '').includes(term);
        row.style.display = visible ? '' : 'none';
    });
});

// Initialise count (pre-selected assets)
updateCount();

// Scroll to first pre-selected row (if any)
const firstChecked = document.querySelector('.asset-checkbox:checked');
if (firstChecked) {
    firstChecked.closest('tr').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<?php
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo 'Erro: Layout principal não encontrado.';
    echo $content;
}
?>
