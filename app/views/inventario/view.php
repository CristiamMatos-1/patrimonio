<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$invId = (int) ($inv['id'] ?? 0);
$percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h5 mb-1">Inventário #<?= $invId ?></h1>
        <div class="text-muted small">Setor <?= htmlspecialchars((string) ($inv['setor'] ?? '')) ?> • <?= $done ?> / <?= $total ?> (<?= $percent ?>%)</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/inventario')) ?>">Voltar</a>
        <?php if (empty($inv['finalizado_em'])): ?>
            <form method="post" action="<?= htmlspecialchars(Http::path('/inventario/finalizar')) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="inventario_id" value="<?= $invId ?>">
                <button class="btn btn-outline-danger btn-sm" type="submit">Finalizar</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="progress mb-3" role="progressbar" aria-label="Progresso" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
    <div class="progress-bar" style="width: <?= $percent ?>%"></div>
</div>

<?php if (empty($inv['finalizado_em'])): ?>
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="border rounded bg-white p-3">
                <div class="fw-semibold mb-2">Conferir por tombamento</div>
                <form method="post" action="<?= htmlspecialchars(Http::path('/inventario/conferir')) ?>" class="row g-2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="inventario_id" value="<?= $invId ?>">
                    <div class="col-12 col-md-8">
                        <input class="form-control" name="tombamento" inputmode="numeric" placeholder="Tombamento" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <button class="btn btn-primary w-100" type="submit">Conferir</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="border rounded bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Conferir via câmera (QR)</div>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="btnScan">Iniciar</button>
                </div>
                <video id="video" class="w-100 rounded border" playsinline muted></video>
                <div class="text-muted small mt-2" id="scanStatus">Aguardando…</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h6 mb-0">Itens</h2>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/inventario/ver')) ?>?id=<?= $invId ?>&pendentes=<?= $onlyPending ? '0' : '1' ?>">
            <?= $onlyPending ? 'Ver todos' : 'Ver pendentes' ?>
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
        <thead>
        <tr>
            <th>Tombamento</th>
            <th>Descrição</th>
            <th>Localização</th>
            <th>Responsável</th>
            <th>Conferido em</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($itens ?? []) as $it): ?>
            <tr>
                <td><?= (int) $it['tombamento'] ?></td>
                <td><?= htmlspecialchars((string) $it['descricao']) ?></td>
                <td><?= htmlspecialchars((string) ($it['localizacao'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($it['responsavel'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($it['conferido_em'] ?? '')) ?></td>
                <td class="text-end">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/ver')) ?>?id=<?= (int) $it['bem_id'] ?>">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(() => {
  const invId = <?= json_encode($invId) ?>;
  const csrf = <?= json_encode($csrf_token) ?>;
  const basePath = <?= json_encode((string) ($config['base_path'] ?? '')) ?>;
  const btn = document.getElementById('btnScan');
  const video = document.getElementById('video');
  const status = document.getElementById('scanStatus');
  if (!btn || !video || !status) return;

  let stream = null;
  let detector = null;
  let running = false;
  let last = '';
  let lastAt = 0;
  const scanCanvas = document.createElement('canvas');
  const scanCtx = scanCanvas.getContext('2d', { willReadFrequently: true });

  async function conferir(payload) {
    const body = new URLSearchParams({ csrf_token: csrf, inventario_id: String(invId), ...payload });
    await fetch(<?= json_encode(Http::path('/inventario/conferir')) ?>, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
      credentials: 'same-origin'
    });
    window.location.reload();
  }

  function parseCode(value) {
    const v = String(value || '').trim();
    if (!v) return null;
    if (/^https?:\/\//i.test(v)) {
      try {
        const u = new URL(v);
        const expected1 = '/bens/ver';
        const expected2 = basePath ? (basePath + expected1) : expected1;
        if ((u.pathname === expected1 || u.pathname === expected2) && u.searchParams.get('id')) {
          return { bem_id: u.searchParams.get('id') };
        }
      } catch (e) {}
    }
    if (/^\d+$/.test(v)) {
      return { tombamento: v };
    }
    return null;
  }

  function stopCamera() {
    running = false;
    if (stream) {
      for (const t of stream.getTracks()) t.stop();
      stream = null;
    }
    detector = null;
    btn.textContent = 'Iniciar';
  }

  async function tickBarcodeDetector() {
    if (!running || !detector) return;
    try {
      const barcodes = await detector.detect(video);
      for (const b of barcodes) {
        const raw = b.rawValue || '';
        const now = Date.now();
        if (raw === last && (now - lastAt) < 2000) continue;
        last = raw;
        lastAt = now;
        const parsed = parseCode(raw);
        if (!parsed) {
          status.textContent = 'QR lido, mas não reconhecido.';
          continue;
        }
        status.textContent = 'Conferindo…';
        stopCamera();
        await conferir(parsed);
        return;
      }
    } catch (e) {}
    requestAnimationFrame(tickBarcodeDetector);
  }

  function tickJsQr() {
    if (!running || !scanCtx) return;
    try {
      const w = video.videoWidth || 0;
      const h = video.videoHeight || 0;
      if (w > 0 && h > 0) {
        scanCanvas.width = w;
        scanCanvas.height = h;
        scanCtx.drawImage(video, 0, 0, w, h);
        const imageData = scanCtx.getImageData(0, 0, w, h);
        const code = window.jsQR ? window.jsQR(imageData.data, w, h, { inversionAttempts: 'dontInvert' }) : null;
        const raw = code && code.data ? String(code.data) : '';
        if (raw) {
          const now = Date.now();
          if (!(raw === last && (now - lastAt) < 2000)) {
            last = raw;
            lastAt = now;
            const parsed = parseCode(raw);
            if (parsed) {
              status.textContent = 'Conferindo…';
              stopCamera();
              conferir(parsed);
              return;
            }
            status.textContent = 'QR lido, mas não reconhecido.';
          }
        }
      }
    } catch (e) {}
    requestAnimationFrame(tickJsQr);
  }

  btn.addEventListener('click', async () => {
    if (running) {
      stopCamera();
      status.textContent = 'Parado.';
      return;
    }
    status.textContent = 'Iniciando câmera…';
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
      video.srcObject = stream;
      await video.play();
      running = true;
      btn.textContent = 'Parar';
      status.textContent = 'Aponte para o QR Code.';

      if ('BarcodeDetector' in window) {
        try {
          detector = new BarcodeDetector({ formats: ['qr_code'] });
          requestAnimationFrame(tickBarcodeDetector);
          return;
        } catch (e) {}
      }

      if (window.jsQR) {
        requestAnimationFrame(tickJsQr);
        return;
      }

      stopCamera();
      status.textContent = 'Navegador sem suporte a leitura automática. Use o tombamento.';
    } catch (e) {
      stopCamera();
      status.textContent = 'Não foi possível abrir a câmera. Verifique permissão e HTTPS.';
    }
  });
})();
</script>
