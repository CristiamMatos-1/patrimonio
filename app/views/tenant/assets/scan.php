<?php ob_start(); ?>

<div class="max-w-4xl mx-auto surface rounded-lg p-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold">Buscar Patrimônio por QR Code</h2>
            <p class="text-sm text-gray-600">Use a câmera do celular, envie uma imagem ou cole o código para encontrar o patrimônio.</p>
        </div>
        <a href="<?= APP_URL ?>/assets" class="text-blue-600 font-semibold hover:underline">Voltar à lista</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="surface rounded-lg p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <button id="btnScan" type="button" class="w-full bg-slate-900 text-white font-semibold py-3 rounded hover:bg-slate-800 transition">Iniciar câmera</button>
                </div>
                <div>
                    <label for="fileInput" class="block text-sm font-semibold mb-2 text-gray-700">Enviar foto do QR Code</label>
                    <input id="fileInput" type="file" accept="image/*" class="w-full text-sm text-gray-700" />
                </div>
            </div>

            <div class="mt-6">
                <label for="manualInput" class="block text-sm font-semibold mb-2 text-gray-700">Colar código ou URL do QR Code</label>
                <div class="flex gap-2">
                    <input id="manualInput" type="text" class="flex-1 px-4 py-2 rounded border focus:border-slate-900 outline-none" placeholder="Cole o URL ou número do patrimônio aqui" />
                    <button id="btnManual" type="button" class="inline-flex items-center justify-center px-4 py-2 rounded bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">Buscar</button>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border">
                <video id="video" class="w-full h-80 bg-black" playsinline muted></video>
            </div>

            <div id="scanStatus" class="mt-4 text-sm text-gray-600">Aguardando ação. Permita o uso da câmera e aponte para o QR Code.</div>
        </div>

        <div class="surface rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-3">Como funciona</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 space-y-2">
                <li>Use a câmera do celular para digitalizar o QR Code.</li>
                <li>Envie uma imagem salva com o QR Code do patrimônio.</li>
                <li>Cole o link ou o número se o QR Code não estiver funcionando.</li>
                <li>O sistema mostrará o patrimônio correspondente automaticamente.</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(() => {
    const btnScan = document.getElementById('btnScan');
    const fileInput = document.getElementById('fileInput');
    const manualInput = document.getElementById('manualInput');
    const btnManual = document.getElementById('btnManual');
    const video = document.getElementById('video');
    const status = document.getElementById('scanStatus');

    let stream = null;
    let running = false;
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    let lastRaw = '';
    let lastAt = 0;

    function toUrl(parsed) {
        if (!parsed) {
            return null;
        }
        if (parsed.hash) {
            return '<?= APP_URL ?>/assets/view?hash=' + encodeURIComponent(parsed.hash);
        }
        if (parsed.id) {
            return '<?= APP_URL ?>/assets/view?id=' + encodeURIComponent(parsed.id);
        }
        if (parsed.asset_number) {
            return '<?= APP_URL ?>/assets/view?asset_number=' + encodeURIComponent(parsed.asset_number);
        }
        return null;
    }

    function parseCode(value) {
        const raw = String(value || '').trim();
        if (!raw) {
            return null;
        }

        if (/^https?:\/\//i.test(raw)) {
            try {
                const url = new URL(raw);
                const assetView = '/assets/view';
                if (url.pathname.endsWith(assetView) && url.searchParams.get('hash')) {
                    return { hash: url.searchParams.get('hash') };
                }
                if (url.pathname.endsWith(assetView) && url.searchParams.get('id')) {
                    return { id: url.searchParams.get('id') };
                }
            } catch (error) {
                // ignore
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

    function redirectTo(code) {
        const parsed = parseCode(code);
        if (!parsed) {
            status.textContent = 'QR Code não reconhecido. Cole o link ou use outra imagem.';
            return;
        }

        const redirectUrl = toUrl(parsed);
        if (!redirectUrl) {
            status.textContent = 'Código válido, mas não foi possível interpretar o conteúdo.';
            return;
        }

        window.location.href = redirectUrl;
    }

    function stopCamera() {
        running = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        btnScan.textContent = 'Iniciar câmera';
    }

    async function scanFrame() {
        if (!running) {
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
            const raw = String(code.data).trim();
            const now = Date.now();
            if (raw && raw !== lastRaw || now - lastAt > 2000) {
                lastRaw = raw;
                lastAt = now;
                status.textContent = 'QR Code capturado. Verificando...';
                stopCamera();
                redirectTo(raw);
                return;
            }
        }

        requestAnimationFrame(scanFrame);
    }

    btnScan.addEventListener('click', async () => {
        if (running) {
            stopCamera();
            status.textContent = 'Scanner parado.';
            return;
        }

        status.textContent = 'Aguardando permissão da câmera...';

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            await video.play();
            running = true;
            btnScan.textContent = 'Parar câmera';
            status.textContent = 'Aponte a câmera para o QR Code.';
            requestAnimationFrame(scanFrame);
        } catch (error) {
            status.textContent = 'Não foi possível acessar a câmera. Verifique permissões ou use uma imagem.';
        }
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files && fileInput.files[0];
        if (!file) {
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            const image = new Image();
            image.onload = () => {
                canvas.width = image.width;
                canvas.height = image.height;
                ctx.drawImage(image, 0, 0, image.width, image.height);
                const imageData = ctx.getImageData(0, 0, image.width, image.height);
                const code = window.jsQR ? window.jsQR(imageData.data, image.width, image.height, { inversionAttempts: 'dontInvert' }) : null;
                if (code && code.data) {
                    status.textContent = 'QR Code lido. Redirecionando...';
                    redirectTo(code.data);
                } else {
                    status.textContent = 'Não foi possível ler o QR Code da imagem.';
                }
            };
            image.src = reader.result;
        };
        reader.readAsDataURL(file);
    });

    btnManual.addEventListener('click', () => {
        redirectTo(manualInput.value);
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
