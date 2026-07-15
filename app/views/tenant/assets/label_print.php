<?php
// Standalone print page — does not use the main layout
$appUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>Etiquetas de Patrimônio</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f0f0f0;
        }

        .no-print {
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .no-print h1 { font-size: 15px; font-weight: 600; }

        .no-print .actions { display: flex; gap: 12px; align-items: center; }

        .no-print button {
            background: #3b82f6;
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .no-print a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
        }

        .no-print a:hover { color: #fff; }

        .labels-wrapper {
            padding: 10mm;
            min-height: calc(100vh - 46px);
        }

        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 4mm;
        }

        /* ─── Etiqueta 3 cm × 3 cm ─── */
        .label {
            width: 3cm;
            height: 3cm;
            border: 0.5pt solid #aaa;
            padding: 0.8mm 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
        }

        .label-title {
            font-size: 5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6pt;
            color: #000;
            line-height: 1.1;
            width: 100%;
        }

        .label-qr img {
            width: 1.7cm;
            height: 1.7cm;
            display: block;
        }

        .label-qr-empty {
            width: 1.7cm;
            height: 1.7cm;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5pt;
            color: #999;
        }

        .label-number {
            font-size: 7pt;
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            line-height: 1.1;
        }

        .label-date {
            font-size: 4.5pt;
            color: #444;
            line-height: 1.1;
        }

        /* ─── Print ─── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            body { background: #fff; }

            .no-print { display: none !important; }

            .labels-wrapper { padding: 0; }

            .labels-grid { gap: 2mm; }

            .label {
                border: 0.5pt solid #000;
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <h1>
        Etiquetas de Patrimônio
        — <?= count($assets ?? []) ?> etiqueta<?= count($assets ?? []) !== 1 ? 's' : '' ?>
    </h1>
    <div class="actions">
        <a href="javascript:window.close()">← Fechar</a>
        <button onclick="window.print()">🖨️&nbsp; Imprimir</button>
    </div>
</div>

<div class="labels-wrapper">
    <div class="labels-grid">
        <?php foreach ($assets ?? [] as $asset):
            $qrData = '';
            if (!empty($asset['qr_code_hash'])) {
                $qrData = $appUrl . '/assets/view?hash=' . urlencode($asset['qr_code_hash']);
            } elseif (!empty($asset['asset_number'])) {
                $qrData = $appUrl . '/assets/view?asset_number=' . urlencode($asset['asset_number']);
            }

            $qrApiUrl = $qrData
                ? 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=2&data=' . urlencode($qrData)
                : '';

            $acqDate = '';
            if (!empty($asset['acquisition_date'])) {
                try {
                    $acqDate = (new DateTime($asset['acquisition_date']))->format('d/m/Y');
                } catch (Exception $e) {
                    $acqDate = (string) $asset['acquisition_date'];
                }
            }
        ?>
        <div class="label">
            <div class="label-title">Patrimônio</div>
            <div class="label-qr">
                <?php if ($qrApiUrl): ?>
                    <img src="<?= htmlspecialchars($qrApiUrl) ?>"
                         alt="QR <?= htmlspecialchars($asset['asset_number'] ?? '') ?>">
                <?php else: ?>
                    <div class="label-qr-empty">Sem QR</div>
                <?php endif; ?>
            </div>
            <div class="label-number"><?= htmlspecialchars($asset['asset_number'] ?? '') ?></div>
            <?php if ($acqDate): ?>
                <div class="label-date"><?= htmlspecialchars($acqDate) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function () {
    var imgs = document.querySelectorAll('.label-qr img');
    if (imgs.length === 0) {
        setTimeout(function () { window.print(); }, 300);
        return;
    }
    var total = imgs.length;
    var done = 0;
    function onLoad() {
        done++;
        if (done >= total) setTimeout(function () { window.print(); }, 400);
    }
    imgs.forEach(function (img) {
        if (img.complete) { onLoad(); }
        else {
            img.addEventListener('load', onLoad);
            img.addEventListener('error', onLoad);
        }
    });
})();
</script>
</body>
</html>
