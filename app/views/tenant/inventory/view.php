<?php ob_start(); ?>

<?php
$inventoryId = (int) ($session['id'] ?? 0);
$percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;
?>

<div class="max-w-6xl mx-auto surface rounded-lg p-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold">Inventário #<?= $inventoryId ?></h2>
            <p class="text-sm text-gray-600">Setor <?= htmlspecialchars((string) ($session['sector'] ?? '')) ?> • <?= $done ?> de <?= $total ?> itens conferidos (<?= $percent ?>%)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/inventory" class="text-blue-600 font-semibold hover:underline">Voltar</a>
            <?php if (!empty($session['finalized_at'])): ?>
                <span class="inline-flex items-center rounded bg-red-100 text-red-700 px-3 py-1 text-xs">Finalizado</span>
            <?php elseif ($isAdmin): ?>
                <form action="<?= APP_URL ?>/inventory/finalize" method="POST" class="inline-block">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
                    <input type="hidden" name="inventory_id" value="<?= $inventoryId ?>">
                    <button type="submit" class="rounded bg-red-600 text-white px-4 py-2 text-sm hover:bg-red-700 transition">Finalizar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr] mb-6">
        <div class="surface rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Verificar patrimônio</h3>
                <div class="text-sm text-gray-500">Use QR, tombamento ou selecione o item.</div>
            </div>

            <?php if (empty($session['finalized_at'])): ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <form action="<?= APP_URL ?>/inventory/verify" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Lib\Csrf::token()) ?>">
                        <input type="hidden" name="inventory_id" value="<?= $inventoryId ?>">

                        <div>
                            <label for="asset_number" class="block text-sm font-semibold mb-1 text-gray-700">Número de Tombamento</label>
                            <input id="asset_number" name="asset_number" type="text" class="w-full px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Ex: 12345">
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white px-4 py-3 rounded hover:bg-slate-800 transition">Confirmar</button>
                    </form>

                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Scanner de QR Code</label>
                        <button id="btnScan" type="button" class="w-full bg-slate-900 text-white px-4 py-3 rounded hover:bg-slate-800 transition">Iniciar câmera</button>
                        <div class="mt-4 rounded border overflow-hidden">
                            <video id="video" class="w-full h-64 bg-black" playsinline muted></video>
                        </div>
                        <p id="scanStatus" class="mt-3 text-sm text-gray-600">Aguardando leitura do QR Code...</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-sm text-gray-600">Inventário finalizado. Nenhuma conferência adicional é permitida.</div>
            <?php endif; ?>
        </div>

        <div class="surface rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Resumo</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Total de itens</span>
                    <span class="font-semibold"><?= $total ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Itens conferidos</span>
                    <span class="font-semibold"><?= $done ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Itens pendentes</span>
                    <span class="font-semibold"><?= max(0, $total - $done) ?></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="h-full bg-slate-900" style="width: <?= $percent ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="surface rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Itens do inventário</h3>
            <a href="<?= APP_URL ?>/inventory/view?id=<?= $inventoryId ?>&pendentes=<?= $onlyPending ? '0' : '1' ?>" class="text-sm text-blue-600 hover:underline"><?= $onlyPending ? 'Ver todos' : 'Ver pendentes' ?></a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="p-3 font-semibold text-gray-600">Tombamento</th>
                        <th class="p-3 font-semibold text-gray-600">Descrição</th>
                        <th class="p-3 font-semibold text-gray-600">Localização</th>
                        <th class="p-3 font-semibold text-gray-600">Responsável</th>
                        <th class="p-3 font-semibold text-gray-600">Conferido</th>
                        <th class="p-3 font-semibold text-gray-600">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($items ?? []) as $item): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium"><?= htmlspecialchars((string) ($item['asset_number'] ?? '-')) ?></td>
                            <td class="p-3 text-gray-700"><?= htmlspecialchars((string) ($item['asset_name'] ?? '-')) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars((string) ($item['location'] ?? '-')) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars((string) ($item['current_responsible'] ?? '-')) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars((string) ($item['checked_at'] ?? '-')) ?></td>
                            <td class="p-3 text-right">
                                <a href="<?= APP_URL ?>/assets/view?id=<?= (int) $item['asset_id'] ?>" class="text-blue-600 hover:underline">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(() => {
    const isFinalized = <?= json_encode(!empty($session['finalized_at'])) ?>;
    if (isFinalized) {
        return;
    }

    const btnScan = document.getElementById('btnScan');
    const video = document.getElementById('video');
    const scanStatus = document.getElementById('scanStatus');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    let stream = null;
    let scanning = false;
    let lastValue = '';
    let lastTime = 0;

    function parseCode(value) {
        const raw = String(value || '').trim();
        if (!raw) {
            return null;
        }

        if (/^https?:\/\//i.test(raw)) {
            try {
                const url = new URL(raw);
                if (url.pathname.endsWith('/assets/view') && url.searchParams.has('id')) {
                    return { id: url.searchParams.get('id') };
                }
                if (url.pathname.endsWith('/assets/view') && url.searchParams.has('hash')) {
                    return { hash: url.searchParams.get('hash') };
                }
            } catch (e) {
                return null;
            }
        }

        if (/^[0-9]+$/.test(raw)) {
            return { asset_number: raw };
        }

        if (/^[a-f0-9]{64}$/i.test(raw)) {
            return { hash: raw };
        }

        return null;
    }

    function redirect(parsed) {
        if (!parsed) {
            scanStatus.textContent = 'Código não reconhecido. Tente novamente.';
            return;
        }

        let url = null;
        if (parsed.id) {
            url = '<?= APP_URL ?>/assets/view?id=' + encodeURIComponent(parsed.id);
        } else if (parsed.hash) {
            url = '<?= APP_URL ?>/assets/view?hash=' + encodeURIComponent(parsed.hash);
        } else if (parsed.asset_number) {
            url = '<?= APP_URL ?>/assets/view?asset_number=' + encodeURIComponent(parsed.asset_number);
        }

        if (url) {
            window.location.href = url;
        } else {
            scanStatus.textContent = 'QR Code válido, mas não foi possível redirecionar.';
        }
    }

    async function stopCamera() {
        scanning = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        btnScan.textContent = 'Iniciar câmera';
    }

    async function scanFrame() {
        if (!scanning) {
            return;
        }
        if (video.readyState !== video.HAVE_ENOUGH_DATA) {
            requestAnimationFrame(scanFrame);
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = window.jsQR ? window.jsQR(imageData.data, canvas.width, canvas.height, { inversionAttempts: 'dontInvert' }) : null;

        if (code && code.data) {
            const value = code.data.trim();
            const now = Date.now();
            if (value && value !== lastValue || now - lastTime > 2000) {
                lastValue = value;
                lastTime = now;
                const parsed = parseCode(value);
                if (parsed) {
                    scanStatus.textContent = 'QR Code lido. Redirecionando...';
                    await stopCamera();
                    redirect(parsed);
                    return;
                }
            }
        }

        requestAnimationFrame(scanFrame);
    }

    btnScan.addEventListener('click', async () => {
        if (scanning) {
            await stopCamera();
            scanStatus.textContent = 'Scanner parado.';
            return;
        }

        scanStatus.textContent = 'Aguardando permissão da câmera...';
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            await video.play();
            scanning = true;
            btnScan.textContent = 'Parar câmera';
            scanStatus.textContent = 'Aponte para o QR Code.';
            requestAnimationFrame(scanFrame);
        } catch (error) {
            scanStatus.textContent = 'Não foi possível acessar a câmera. Verifique permissões.';
        }
    });
})();
</script>

<?php 
$content = ob_get_clean();
if (isset($layoutPath) && file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erro: Layout principal não encontrado.";
    echo $content;
}
