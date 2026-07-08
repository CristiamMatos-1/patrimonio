<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$id = (int) ($bem['id'] ?? 0);
$tombamento = (int) ($bem['tombamento'] ?? 0);
$bemUrl = Http::path('/bens/ver') . '?id=' . $id;
$bemUrlAbs = ((string) ($config['base_url'] ?? '') !== '') ? ((string) $config['base_url'] . $bemUrl) : $bemUrl;
$fotoUrl = (string) ($bem['foto_url'] ?? '');
$fotoSrc = $fotoUrl !== '' ? Http::path($fotoUrl) : '';
?>

<style>
@media print {
  .no-print { display: none !important; }
  body { background: #fff !important; }
  .card, .card-body { box-shadow: none !important; border: none !important; }
}
</style>

<div class="no-print d-flex justify-content-between align-items-center mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($bemUrl) ?>">Voltar</a>
    <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimir</button>
</div>

<div class="border rounded p-3 bg-white" style="max-width: 420px;">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted small">Tombamento</div>
            <div class="h4 mb-1"><?= $tombamento ?></div>
            <div class="text-muted small">Descrição</div>
            <div class="fw-semibold"><?= htmlspecialchars((string) ($bem['descricao'] ?? '')) ?></div>
        </div>
        <div class="text-end">
            <img id="qrcodeImg" class="border rounded" style="width: 180px; height: 180px;" alt="QR Code">
            <canvas id="qrcodeCanvas" class="d-none"></canvas>
        </div>
    </div>

    <?php if ($fotoSrc !== ''): ?>
        <div class="mt-3">
            <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Foto" class="img-fluid rounded border" style="max-height: 220px;" onerror="this.style.display='none';document.getElementById('fotoFallback').style.display='block';">
            <div id="fotoFallback" class="small text-muted" style="display:none;">
                <div>Não foi possível exibir esta imagem no navegador.</div>
                <a href="<?= htmlspecialchars($fotoSrc) ?>" target="_blank" rel="noreferrer">Abrir/baixar foto</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-muted small mt-3"><?= htmlspecialchars($bemUrlAbs) ?></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script>
(() => {
  const img = document.getElementById('qrcodeImg');
  const canvas = document.getElementById('qrcodeCanvas');
  const url = <?= json_encode($bemUrlAbs) ?>;
  const fallback = () => {
    if (!img) return;
    img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(url);
  };
  if (!canvas || !img) return fallback();
  if (typeof QRCode === 'undefined' || !QRCode.toCanvas) return fallback();
  QRCode.toCanvas(canvas, url, { width: 180, margin: 1 }, (err) => {
    if (err) return fallback();
    try {
      img.src = canvas.toDataURL('image/png');
    } catch (e) {
      fallback();
    }
  });
})();
</script>
