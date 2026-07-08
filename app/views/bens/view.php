<?php declare(strict_types=1); ?>

<?php
use App\Lib\Auth;
use App\Lib\Http;
?>

<?php
$id = (int) ($bem['id'] ?? 0);
$tombamento = (int) ($bem['tombamento'] ?? 0);
$valor = isset($bem['valor_aquisicao']) ? (float) $bem['valor_aquisicao'] : null;
$vida = isset($bem['vida_util']) ? (int) $bem['vida_util'] : null;
$dataAq = (string) ($bem['data_aquisicao'] ?? '');
$deprAnual = ($valor !== null && $vida !== null && $vida > 0) ? ($valor / $vida) : null;
$anos = null;
if ($deprAnual !== null && $dataAq !== '') {
    try {
        $d1 = new DateTimeImmutable($dataAq);
        $d2 = new DateTimeImmutable('now');
        $diff = $d1->diff($d2);
        $anos = (int) floor(($diff->days ?? 0) / 365);
        $anos = min($anos, $vida);
    } catch (Throwable $e) {
        $anos = null;
    }
}
$deprAcumulada = ($deprAnual !== null && $anos !== null) ? ($deprAnual * $anos) : null;
$valorAtual = ($valor !== null && $deprAcumulada !== null) ? max(0, $valor - $deprAcumulada) : null;
$bemUrl = Http::path('/bens/ver') . '?id=' . $id;
$bemUrlAbs = ((string) ($config['base_url'] ?? '') !== '') ? ((string) $config['base_url'] . $bemUrl) : $bemUrl;
$fotoUrl = (string) ($bem['foto_url'] ?? '');
$fotoSrc = $fotoUrl !== '' ? Http::path($fotoUrl) : '';
$localNome = (string) ($bem['local_nome'] ?? '');
$empresaNome = (string) ($bem['empresa_razao_social'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h5 mb-1">Bem #<?= $tombamento ?></h1>
        <div class="text-muted small">ID <?= $id ?> • Status <?= htmlspecialchars((string) ($bem['status'] ?? '')) ?></div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/bens')) ?>">Voltar</a>
        <?php if (Auth::can('bens:editar')): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/editar')) ?>?id=<?= $id ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <?php if (is_array($emprestimoAtual) && (string) ($emprestimoAtual['status'] ?? '') === 'aberto'): ?>
            <div class="border rounded bg-white p-3 mb-3">
                <div class="fw-semibold mb-2">Empréstimo em aberto</div>
                <div class="row g-2">
                    <div class="col-6 col-md-4"><div class="text-muted small">Saída</div><div><?= htmlspecialchars((string) ($emprestimoAtual['data_saida'] ?? '')) ?></div></div>
                    <div class="col-6 col-md-4"><div class="text-muted small">Prevista</div><div><?= htmlspecialchars((string) ($emprestimoAtual['data_prevista'] ?? '')) ?></div></div>
                    <div class="col-12 col-md-4"><div class="text-muted small">Status</div><div><?= htmlspecialchars((string) ($emprestimoAtual['status'] ?? '')) ?></div></div>
                    <div class="col-12 col-md-4"><div class="text-muted small">Retirado por</div><div><?= htmlspecialchars((string) ($emprestimoAtual['retirado_por_nome'] ?? '')) ?></div></div>
                    <div class="col-12 col-md-4"><div class="text-muted small">Contato</div><div><?= htmlspecialchars((string) ($emprestimoAtual['retirado_por_contato'] ?? '')) ?></div></div>
                    <div class="col-12 col-md-4"><div class="text-muted small">Destino</div><div><?= htmlspecialchars((string) (($emprestimoAtual['destino_local_nome'] ?? '') !== '' ? ($emprestimoAtual['destino_local_nome'] . ' (' . ($emprestimoAtual['destino_local_tipo'] ?? '') . ')') : ($emprestimoAtual['destino_endereco'] ?? ''))) ?></div></div>
                </div>
                <?php if (!empty($emprestimoAtual['observacao'])): ?>
                    <div class="text-muted small mt-2">Observação</div>
                    <div><?= nl2br(htmlspecialchars((string) $emprestimoAtual['observacao'])) ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (Auth::can('emprestimos:gerir') || Auth::can('movimentacoes:criar')): ?>
            <div class="border rounded bg-white p-3 mb-3">
                <div class="fw-semibold mb-2">Ações</div>
                <div class="row g-2">
                    <?php if (Auth::can('emprestimos:gerir') && (string) ($bem['status'] ?? '') === 'ativo'): ?>
                        <div class="col-12 col-md-6">
                            <form method="post" action="<?= htmlspecialchars(Http::path('/bens/emprestar')) ?>" class="vstack gap-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="bem_id" value="<?= $id ?>">
                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small">Quem retirou</label>
                                        <input class="form-control form-control-sm" name="retirado_por_nome" placeholder="Nome">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small">Documento</label>
                                        <input class="form-control form-control-sm" name="retirado_por_documento" placeholder="CPF/RG">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small">Contato</label>
                                        <input class="form-control form-control-sm" name="retirado_por_contato" placeholder="Telefone/WhatsApp">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small">Data prevista</label>
                                        <input class="form-control form-control-sm" type="date" name="data_prevista">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Destino (local)</label>
                                        <select class="form-select form-select-sm" name="destino_local_id">
                                            <option value="">Selecione</option>
                                            <?php foreach (($locais ?? []) as $l): ?>
                                                <option value="<?= (int) $l['id'] ?>">
                                                    <?= htmlspecialchars((string) $l['empresa_razao_social']) ?> • <?= htmlspecialchars((string) $l['nome']) ?> (<?= htmlspecialchars((string) $l['tipo']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Destino (endereço, se necessário)</label>
                                        <input class="form-control form-control-sm" name="destino_endereco" placeholder="Opcional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Observação</label>
                                        <input class="form-control form-control-sm" name="observacao" placeholder="Opcional">
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-warning" type="submit">Registrar empréstimo</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (Auth::can('emprestimos:gerir') && (string) ($bem['status'] ?? '') === 'emprestado'): ?>
                        <div class="col-12 col-md-6">
                            <form method="post" action="<?= htmlspecialchars(Http::path('/bens/devolver')) ?>" class="vstack gap-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="bem_id" value="<?= $id ?>">
                                <div>
                                    <label class="form-label small">Observação</label>
                                    <input class="form-control form-control-sm" name="observacao" placeholder="Opcional">
                                </div>
                                <button class="btn btn-sm btn-success" type="submit">Registrar devolução</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (Auth::can('movimentacoes:criar') && (string) ($bem['status'] ?? '') !== 'baixado'): ?>
                        <div class="col-12 col-md-6">
                            <form method="post" action="<?= htmlspecialchars(Http::path('/bens/baixar')) ?>" class="vstack gap-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="bem_id" value="<?= $id ?>">
                                <div>
                                    <label class="form-label small">Motivo/observação</label>
                                    <input class="form-control form-control-sm" name="observacao" placeholder="Opcional">
                                </div>
                                <button class="btn btn-sm btn-danger" type="submit">Baixar bem</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-2">
            <div class="col-6 col-md-4"><div class="text-muted small">Código interno</div><div><?= htmlspecialchars((string) ($bem['codigo_interno'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Categoria</div><div><?= htmlspecialchars((string) ($bem['categoria'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Marca</div><div><?= htmlspecialchars((string) ($bem['marca'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Modelo</div><div><?= htmlspecialchars((string) ($bem['modelo'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Nº série</div><div><?= htmlspecialchars((string) ($bem['numero_serie'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Valor aquisição</div><div><?= $valor !== null ? 'R$ ' . number_format($valor, 2, ',', '.') : '' ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Data aquisição</div><div><?= htmlspecialchars((string) ($bem['data_aquisicao'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Vida útil</div><div><?= htmlspecialchars((string) ($bem['vida_util'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Centro de custo</div><div><?= htmlspecialchars((string) ($bem['centro_custo'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Setor</div><div><?= htmlspecialchars((string) ($bem['setor'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Local</div><div><?= htmlspecialchars(trim($empresaNome . ($empresaNome !== '' && $localNome !== '' ? ' • ' : '') . $localNome)) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Localização</div><div><?= htmlspecialchars((string) ($bem['localizacao'] ?? '')) ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Responsável</div><div><?= htmlspecialchars((string) ($bem['responsavel'] ?? '')) ?></div></div>
        </div>

        <?php if (!empty($bem['descricao'])): ?>
            <hr class="my-3">
            <div class="text-muted small">Descrição</div>
            <div><?= nl2br(htmlspecialchars((string) $bem['descricao'])) ?></div>
        <?php endif; ?>

        <?php if (!empty($bem['observacoes'])): ?>
            <hr class="my-3">
            <div class="text-muted small">Observações</div>
            <div><?= nl2br(htmlspecialchars((string) $bem['observacoes'])) ?></div>
        <?php endif; ?>

        <hr class="my-3">
        <h2 class="h6 mb-2">Depreciação (linear)</h2>
        <div class="row g-2">
            <div class="col-6 col-md-4"><div class="text-muted small">Anual</div><div><?= $deprAnual !== null ? 'R$ ' . number_format($deprAnual, 2, ',', '.') : '' ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Anos considerados</div><div><?= $anos !== null ? (int) $anos : '' ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Acumulada</div><div><?= $deprAcumulada !== null ? 'R$ ' . number_format($deprAcumulada, 2, ',', '.') : '' ?></div></div>
            <div class="col-6 col-md-4"><div class="text-muted small">Valor atual</div><div><?= $valorAtual !== null ? 'R$ ' . number_format($valorAtual, 2, ',', '.') : '' ?></div></div>
        </div>

        <hr class="my-3">
        <h2 class="h6 mb-2">Histórico</h2>
        <?php if (Auth::can('movimentacoes:criar')): ?>
            <form method="post" action="<?= htmlspecialchars(Http::path('/bens/movimentacao')) ?>" class="row g-2 mb-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="bem_id" value="<?= $id ?>">
                <div class="col-12 col-md-3">
                    <input class="form-control form-control-sm" name="tipo" placeholder="Tipo" required>
                </div>
                <div class="col-12 col-md-7">
                    <input class="form-control form-control-sm" name="observacao" placeholder="Observação (opcional)">
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-outline-primary btn-sm w-100" type="submit">Registrar</button>
                </div>
            </form>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Usuário</th>
                    <th>Observação</th>
                    <th>Dados</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($movimentacoes ?? []) as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($m['data'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($m['tipo'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($m['usuario_nome'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($m['observacao'] ?? '')) ?></td>
                        <td class="small text-muted"><?= htmlspecialchars((string) ($m['dados_json'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="border rounded bg-white p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">QR Code</div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($bemUrl) ?>" target="_blank" rel="noreferrer">Abrir</a>
                    <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars(Http::path('/bens/etiqueta')) ?>?id=<?= $id ?>" target="_blank" rel="noreferrer">Imprimir</a>
                </div>
            </div>
            <img id="qrcodeImg" class="img-fluid border rounded" style="max-width: 280px;" alt="QR Code">
            <canvas id="qrcodeCanvas" class="d-none"></canvas>
            <div class="text-muted small mt-2"><?= htmlspecialchars($bemUrl) ?></div>
        </div>

        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Foto</div>
            <?php if ($fotoSrc !== ''): ?>
                <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Foto" class="img-fluid rounded border" onerror="this.style.display='none';document.getElementById('fotoFallback').style.display='block';">
                <div id="fotoFallback" class="small text-muted" style="display:none;">
                    <div>Não foi possível exibir esta imagem no navegador.</div>
                    <a href="<?= htmlspecialchars($fotoSrc) ?>" target="_blank" rel="noreferrer">Abrir/baixar foto</a>
                </div>
            <?php else: ?>
                <div class="text-muted small">Sem foto</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script>
(() => {
  const img = document.getElementById('qrcodeImg');
  const canvas = document.getElementById('qrcodeCanvas');
  const url = <?= json_encode($bemUrlAbs) ?>;
  const fallback = () => {
    if (!img) return;
    img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(url);
  };
  if (!canvas || !img) return fallback();
  if (typeof QRCode === 'undefined' || !QRCode.toCanvas) return fallback();
  QRCode.toCanvas(canvas, url, { width: 280, margin: 1 }, (err) => {
    if (err) return fallback();
    try {
      img.src = canvas.toDataURL('image/png');
    } catch (e) {
      fallback();
    }
  });
})();
</script>
