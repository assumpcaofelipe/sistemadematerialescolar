<?php
$pageTitle = 'Dashboard';

$nomeStatus = [
    'realizado'    => 'Novos',
    'em_andamento' => 'Em processamento',
    'concluido'    => 'Concluídos',
    'cancelado'    => 'Cancelados',
];
$corStatus = [
    'realizado'    => '#ffc107',
    'em_andamento' => '#023e7d',
    'concluido'    => '#198754',
    'cancelado'    => '#dc3545',
];
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Dashboard</h2>
</div>

<!-- Filtros -->
<form method="get" action="/admin/dashboard" class="card card-body bg-light mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Período</label>
            <select name="periodo" class="form-select form-select-sm">
                <option value="7" <?= ($filtros['periodo'] === 7) ? 'selected' : '' ?>>Últimos 7 dias</option>>Últimos 7 dias</option>
                <option value="15" <?= ($filtros['periodo'] === 15) ? 'selected' : '' ?>>Últimos 15 dias</option>
                <option value="30" <?= ($filtros['periodo'] === 30) ? 'selected' : '' ?>>Últimos 30 dias</option>
                <option value="90" <?= ($filtros['periodo'] === 90) ? 'selected' : '' ?>>Últimos 90 dias</option>
                <option value="0" <?= ($filtros['periodo'] === 0) ? 'selected' : '' ?>>Todo o período</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Todos</option>
                <?php foreach ($nomeStatus as $valor => $rotulo): ?>
                    <option value="<?= e($valor) ?>" <?= ($filtros['status'] === $valor) ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Escola</label>
            <select name="escola" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($escolas as $esc): ?>
                    <option value="<?= (int) $esc['id'] ?>"
                        <?= ((int) $esc['id'] === (int) $filtros['escola']) ? 'selected' : '' ?>>
                        <?= e($esc['nome_escola']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Categoria (mais pedidos)</label>
            <select name="categoria" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>"
                        <?= ((int) $cat['id'] === (int) $filtros['categoria']) ? 'selected' : '' ?>>
                        <?= e($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-sm btn-primary flex-fill" type="submit">Filtrar</button>
            <?php if ($filtrosAtivos): ?>
                <a href="/admin/dashboard" class="btn btn-sm btn-outline-secondary">Limpar</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Cards de status -->
<div class="row g-3 mb-4">
    <?php foreach ($nomeStatus as $statusKey => $rotulo): ?>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0" style="border-left: 4px solid <?= e($corStatus[$statusKey]) ?>;">
                <div class="card-body py-3">
                    <div class="fs-3 destaque-numero" style="color:<?= e($corStatus[$statusKey]) ?>"><?= (int) $statusPeriodo[$statusKey] ?></div>
                    <div class="text-muted small"><?= e($rotulo) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Gráfico 1 e 2 -->
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><?= icon('line-chart', '18', '18', 'me-1') ?>Pedidos por dia</div>
            <div class="card-body">
                <canvas id="chartSerie" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><?= icon('bar-chart', '18', '18', 'me-1') ?>Pedidos por status</div>
            <div class="card-body d-flex align-items-center">
                <canvas id="chartStatus" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico 3 e 4 -->
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><?= icon('package', '18', '18', 'me-1') ?>Produtos mais pedidos</div>
            <div class="card-body">
                <canvas id="chartProdutos" height="170"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><?= icon('school', '18', '18', 'me-1') ?>Escolas que mais pedem</div>
            <div class="card-body">
                <canvas id="chartEscolas" height="170"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = 'system-ui, sans-serif';
    Chart.defaults.plugins.legend.labels.boxWidth = 12;

    var serie = <?= json_encode($serie, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
    new Chart(document.getElementById('chartSerie'), {
            type: 'line',
            data: {
                labels: serie.labels,
                datasets: [{
                    label: 'Pedidos',
                    data: serie.data,
                    borderColor: '#023e7d',
                    backgroundColor: 'rgba(2, 62, 125, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Pedidos' } }
                }
            }
        });

        var status = <?= json_encode([
            'labels' => array_values($nomeStatus),
            'data'   => array_values($statusPeriodo),
            'cores'  => array_values($corStatus),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: status.labels,
                datasets: [{
                    data: status.data,
                    backgroundColor: status.cores,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        var produtos = <?= json_encode([
            'labels' => array_map(fn ($p) => mb_strimwidth((string) $p['nome'], 0, 34, '…'), $topProdutos),
            'data'   => array_map(fn ($p) => (int) $p['total'], $topProdutos),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
        new Chart(document.getElementById('chartProdutos'), {
            type: 'bar',
            data: {
                labels: produtos.labels,
                datasets: [{
                    label: 'Unidades pedidas',
                    data: produtos.data,
                    backgroundColor: '#198754',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        var escolas = <?= json_encode([
            'labels' => array_map(fn ($e) => mb_strimwidth((string) ($e['nome_escola'] ?? '—'), 0, 26, '…'), $topEscolas),
            'data'   => array_map(fn ($e) => (int) $e['total_pedidos'], $topEscolas),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
        new Chart(document.getElementById('chartEscolas'), {
            type: 'bar',
            data: {
                labels: escolas.labels,
                datasets: [{
                    label: 'Pedidos',
                    data: escolas.data,
                    backgroundColor: '#ffc107',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
});
</script>

<?php include __DIR__ . '/../layouts/admin_footer.php';