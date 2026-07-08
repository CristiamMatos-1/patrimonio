<?php declare(strict_types=1); ?>

<?php
use App\Lib\Http;

$isEdit = is_array($bem);
$bemId = (int) ($bem['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0"><?= $isEdit ? 'Editar bem' : 'Novo bem' ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(Http::path('/bens')) ?>">Voltar</a>
</div>

<form method="post" action="<?= htmlspecialchars(Http::path((string) ($action ?? '/bens/novo'))) ?>" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $bemId ?>">
    <?php endif; ?>

    <?php if ($isEdit): ?>
        <div class="col-12 col-md-3">
            <label class="form-label">Tombamento</label>
            <input class="form-control" value="<?= (int) ($bem['tombamento'] ?? 0) ?>" readonly>
        </div>
    <?php endif; ?>

    <div class="col-12 col-md-3">
        <label class="form-label">Código interno</label>
        <input class="form-control" name="codigo_interno" value="<?= htmlspecialchars((string) ($bem['codigo_interno'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Descrição</label>
        <input class="form-control" name="descricao" required value="<?= htmlspecialchars((string) ($bem['descricao'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Categoria</label>
        <input class="form-control" name="categoria" value="<?= htmlspecialchars((string) ($bem['categoria'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Marca</label>
        <input class="form-control" name="marca" value="<?= htmlspecialchars((string) ($bem['marca'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Modelo</label>
        <input class="form-control" name="modelo" value="<?= htmlspecialchars((string) ($bem['modelo'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Número de série</label>
        <input class="form-control" name="numero_serie" value="<?= htmlspecialchars((string) ($bem['numero_serie'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Valor de aquisição</label>
        <input class="form-control" name="valor_aquisicao" inputmode="decimal" value="<?= htmlspecialchars((string) ($bem['valor_aquisicao'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Data de aquisição</label>
        <input class="form-control" name="data_aquisicao" type="date" value="<?= htmlspecialchars((string) ($bem['data_aquisicao'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Vida útil (anos)</label>
        <input class="form-control" name="vida_util" inputmode="numeric" value="<?= htmlspecialchars((string) ($bem['vida_util'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Centro de custo</label>
        <input class="form-control" name="centro_custo" value="<?= htmlspecialchars((string) ($bem['centro_custo'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Setor</label>
        <input class="form-control" name="setor" value="<?= htmlspecialchars((string) ($bem['setor'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Local</label>
        <?php $localId = (int) ($bem['local_id'] ?? 0); ?>
        <select class="form-select" name="local_id">
            <option value="">Selecione</option>
            <?php foreach (($locais ?? []) as $l): ?>
                <option value="<?= (int) $l['id'] ?>" <?= $localId === (int) $l['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $l['empresa_razao_social']) ?> • <?= htmlspecialchars((string) $l['nome']) ?> (<?= htmlspecialchars((string) $l['tipo']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Localização</label>
        <input class="form-control" name="localizacao" value="<?= htmlspecialchars((string) ($bem['localizacao'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Responsável atual</label>
        <input class="form-control" name="responsavel" value="<?= htmlspecialchars((string) ($bem['responsavel'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Status</label>
        <?php $status = (string) ($bem['status'] ?? 'ativo'); ?>
        <select class="form-select" name="status">
            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="emprestado" <?= $status === 'emprestado' ? 'selected' : '' ?>>Emprestado</option>
            <option value="baixado" <?= $status === 'baixado' ? 'selected' : '' ?>>Baixado</option>
        </select>
    </div>

    <div class="col-12 col-md-8">
        <label class="form-label">Foto</label>
        <input class="form-control" name="foto" type="file" accept="image/*" capture="environment">
        <?php if (!empty($bem['foto_url'])): ?>
            <div class="mt-2">
                <img src="<?= htmlspecialchars(Http::path((string) $bem['foto_url'])) ?>" alt="Foto" class="img-fluid rounded border" style="max-height: 220px;">
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <label class="form-label">Observações</label>
        <textarea class="form-control" name="observacoes" rows="4"><?= htmlspecialchars((string) ($bem['observacoes'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
</form>
