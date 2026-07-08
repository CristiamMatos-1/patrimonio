<?php declare(strict_types=1); ?>

<?php use App\Lib\Http; ?>

<h1 class="h5 mb-3">Relatórios</h1>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Bens por setor</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=por_setor">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=por_setor">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Bens por centro de custo</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=por_centro_custo">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=por_centro_custo">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Bens emprestados</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=emprestados">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=emprestados">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Bens vencidos (prazo)</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=vencidos">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=vencidos">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Bens sem responsável</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=sem_responsavel">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=sem_responsavel">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="border rounded bg-white p-3">
            <div class="fw-semibold mb-2">Relatório geral</div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/csv')) ?>?tipo=geral">Excel (CSV)</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/relatorios/pdf')) ?>?tipo=geral">PDF</a>
            </div>
        </div>
    </div>
</div>
