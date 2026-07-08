<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<h1 class="h5 mb-3">Painel</h1>

<div class="row g-3">
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Total</div>
            <div class="h4 mb-0"><?= (int) ($counts['bens_total'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Ativos</div>
            <div class="h4 mb-0"><?= (int) ($counts['bens_ativos'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Emprestados</div>
            <div class="h4 mb-0"><?= (int) ($counts['bens_emprestados'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Baixados</div>
            <div class="h4 mb-0"><?= (int) ($counts['bens_baixados'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Devoluções pendentes</div>
            <div class="h4 mb-0"><?= (int) ($counts['bens_emprestados'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="border rounded p-3 bg-white">
            <div class="text-muted small">Atrasados</div>
            <div class="h4 mb-0"><?= (int) ($atrasados ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <div class="border rounded p-3 bg-white">
            <div class="fw-semibold mb-2">Bens por local (alocados)</div>
            <canvas id="chartLocais"></canvas>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded p-3 bg-white">
            <div class="fw-semibold mb-2">Empréstimos por destino (temporário)</div>
            <canvas id="chartEmprestimos"></canvas>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?= htmlspecialchars(Http::path('/bens')) ?>">Gerenciar bens</a>
    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(Http::path('/relatorios')) ?>">Relatórios</a>
</div>

<?php if (!empty($alertas)): ?>
    <div class="mt-4 border rounded p-3 bg-white">
        <div class="fw-semibold mb-2">Alertas de devolução (atrasados)</div>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Prevista</th>
                    <th>Tombamento</th>
                    <th>Descrição</th>
                    <th>Destino</th>
                    <th>Retirado por</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($alertas ?? []) as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($a['data_prevista'] ?? '')) ?></td>
                        <td><?= (int) ($a['tombamento'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($a['descricao'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($a['destino_local_nome'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string) ($a['retirado_por_nome'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  if (typeof Chart === 'undefined') return;

  const locais = <?= json_encode($porLocal ?? []) ?>;
  const emp = <?= json_encode($emprestimosDestino ?? []) ?>;

  const colors = ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1','#20c997','#0dcaf0','#6c757d','#fd7e14','#6610f2'];

  const makeDoughnut = (id, rows) => {
    const el = document.getElementById(id);
    if (!el) return;
    const labels = rows.map(r => String(r.nome ?? ''));
    const values = rows.map(r => Number(r.quantidade ?? 0));
    new Chart(el, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: labels.map((_, i) => colors[i % colors.length]),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  };

  makeDoughnut('chartLocais', locais);
  makeDoughnut('chartEmprestimos', emp);
})();
</script>
