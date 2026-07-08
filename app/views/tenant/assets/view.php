<?php ob_start(); ?>

<div class="max-w-5xl mx-auto surface rounded-lg p-8">
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold">Detalhes do Patrimônio</h2>
            <p class="text-sm text-gray-500">#<?= htmlspecialchars($asset['asset_number'] ?? '') ?> • <?= htmlspecialchars($asset['name'] ?? '') ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= APP_URL ?>/assets" class="text-blue-600 font-semibold hover:underline">&larr; Voltar à lista</a>
            <a href="<?= APP_URL ?>/assets/edit?id=<?= (int) $asset['id'] ?>" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800 transition">Editar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1.6fr_1fr] gap-6">
        <div class="space-y-6">
            <div class="surface rounded-lg p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Filial</span>
                        <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($asset['branch_name'] ?? '') ?></div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Status</span>
                        <div class="mt-1 text-sm font-semibold">
                            <?php if ($asset['status'] === 'active'): ?>Ativo<?php elseif ($asset['status'] === 'maintenance'): ?>Em manutenção<?php elseif ($asset['status'] === 'borrowed'): ?>Emprestado<?php else: ?>Baixado<?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Localização</span>
                        <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($asset['location'] ?? '') ?></div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Responsável</span>
                        <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($asset['current_responsible'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <div class="surface rounded-lg p-5">
                <h3 class="text-lg font-semibold mb-4">Informações do Bem</h3>
                <?php if (!empty($asset['photo_url'])): ?>
                    <div class="mb-4 flex justify-center">
                        <img src="<?= htmlspecialchars($asset['photo_url']) ?>" alt="Foto do Patrimônio" class="w-full max-w-xs rounded-lg border">
                    </div>
                <?php endif; ?>
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <strong>Categoria:</strong> <?= htmlspecialchars($asset['category'] ?? '') ?>
                    </div>
                    <div>
                        <strong>Marca:</strong> <?= htmlspecialchars($asset['brand'] ?? '') ?>
                    </div>
                    <div>
                        <strong>Modelo:</strong> <?= htmlspecialchars($asset['model'] ?? '') ?>
                    </div>
                    <div>
                        <strong>Serial:</strong> <?= htmlspecialchars($asset['serial_number'] ?? '') ?>
                    </div>
                    <div>
                        <strong>Data aquisição:</strong> <?= htmlspecialchars($asset['acquisition_date'] ?? '') ?>
                    </div>
                    <div>
                        <strong>Valor:</strong> <?= !empty($asset['value']) ? 'R$ ' . number_format((float) $asset['value'], 2, ',', '.') : '-' ?>
                    </div>
                </div>
                <?php if (!empty($asset['description'])): ?>
                    <div class="mt-4">
                        <strong>Descrição:</strong>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line"><?= htmlspecialchars($asset['description']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($asset['observations'])): ?>
                    <div class="mt-4">
                        <strong>Observações:</strong>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line"><?= htmlspecialchars($asset['observations']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($activeLoan)): ?>
                <div class="surface rounded-lg p-5 border border-orange-200 bg-orange-50">
                    <h3 class="text-lg font-semibold mb-3">Empréstimo Ativo</h3>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Retirado por</span>
                            <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($activeLoan['borrowed_by'] ?? '') ?></div>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Data de saída</span>
                            <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($activeLoan['out_date'] ?? '') ?></div>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Local de envio</span>
                            <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($activeLoan['destination_location'] ?? '-') ?></div>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-[0.12em] text-gray-500">Previsão de retorno</span>
                            <div class="mt-1 text-sm text-slate-900"><?= htmlspecialchars($activeLoan['expected_return_date'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="surface rounded-lg p-5">
                <h3 class="text-lg font-semibold mb-4">Histórico de Empréstimos</h3>
                <?php if (empty($loanHistory)): ?>
                    <div class="text-sm text-gray-500">Nenhum empréstimo registrado para este patrimônio.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700">
                                    <th class="px-3 py-2">Retirado por</th>
                                    <th class="px-3 py-2">Saída</th>
                                    <th class="px-3 py-2">Retorno previsto</th>
                                    <th class="px-3 py-2">Local enviado</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loanHistory as $loan): ?>
                                    <tr class="border-t border-slate-200">
                                        <td class="px-3 py-2"><?= htmlspecialchars($loan['borrowed_by'] ?? '') ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($loan['out_date'] ?? '') ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($loan['expected_return_date'] ?? '-') ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($loan['destination_location'] ?? '-') ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($loan['status'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface rounded-lg p-5">
                <h3 class="text-lg font-semibold mb-4">QR Code</h3>
                <div class="rounded border bg-white p-4 text-center">
                    <?php if (!empty($asset['qr_code_hash'])): ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode(APP_URL . '/assets/view?hash=' . $asset['qr_code_hash']) ?>" alt="QR Code" class="mx-auto mb-3">
                        <div class="text-xs text-gray-500 break-all"><?= htmlspecialchars(APP_URL . '/assets/view?hash=' . $asset['qr_code_hash']) ?></div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500">QR Code não disponível.</div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($asset['status'] === 'active'): ?>
                <div class="surface rounded-lg p-5">
                    <h3 class="text-lg font-semibold mb-4">Registrar Empréstimo</h3>
                    <form action="<?= APP_URL ?>/assets/loan" method="POST" class="space-y-4">
                        <input type="hidden" name="asset_id" value="<?= (int) $asset['id'] ?>">

                        <div>
                            <label for="borrowed_by" class="block text-sm font-semibold mb-1 text-gray-700">Retirado por</label>
                            <input id="borrowed_by" name="borrowed_by" type="text" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="out_date" class="block text-sm font-semibold mb-1 text-gray-700">Data de Saída</label>
                                <input id="out_date" name="out_date" type="date" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" required>
                            </div>
                            <div>
                                <label for="expected_return_date" class="block text-sm font-semibold mb-1 text-gray-700">Previsão de Devolução</label>
                                <input id="expected_return_date" name="expected_return_date" type="date" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="destination_location" class="block text-sm font-semibold mb-1 text-gray-700">Local Enviado</label>
                            <input id="destination_location" name="destination_location" type="text" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Informe o local de envio">
                        </div>

                        <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800 transition">Registrar Empréstimo</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo 'Erro: Layout principal não encontrado.';
    echo $content;
}
?>
