<?php $pageTitle = 'Histórico de pedidos'; include __DIR__ . '/../layouts/escola_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="h4 mb-0"><?= icon('inbox', '20', '20', 'me-1') ?>Histórico de pedidos <span class="text-muted fs-6">(<?= $total ?>)</span></h1>
</div>

<form method="get" action="/pedidos" class="row g-2 mb-3">
    <div class="col-12 col-md-4 col-lg-3">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Todos os status</option>
            <?php foreach (\App\Core\StatusPedido::ROTULOS as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>" <?= ($status === $valor) ? 'selected' : '' ?>>
                    <?= e($rotulo) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($status !== ''): ?>
        <div class="col-auto">
            <a href="/pedidos" class="btn btn-outline-secondary">Limpar</a>
        </div>
    <?php endif; ?>
</form>

<?php if ($total === 0): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <?= icon('inbox', '40', '40', 'mb-3') ?>
            <?php if ($status !== ''): ?>
                <p class="mb-1">Nenhum pedido com este status.</p>
                <a href="/pedidos" class="btn btn-outline-secondary mt-2">Limpar filtro</a>
            <?php else: ?>
                <p class="mb-1">Você ainda não fez nenhum pedido.</p>
                <a href="/catalogo" class="btn btn-primary mt-2">Ir para o catálogo</a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pedido</th>
                        <th>Data</th>
                        <th class="text-center" style="width:80px;">Itens</th>
                        <th>Status</th>
                        <th class="table-acoes text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td class="destaque-numero">#<?= (int) $pedido['numero'] ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($pedido['created_at']))) ?></td>
                            <td class="text-center"><?= (int) $pedido['total_itens'] ?></td>
                            <td>
                                <span class="badge text-bg-<?= e(\App\Core\StatusPedido::cor($pedido['status'])) ?>">
                                    <?= icon(\App\Core\StatusPedido::icone($pedido['status']), '14', '14', 'me-1') ?><?= e(\App\Core\StatusPedido::rotulo($pedido['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-outline-primary btn-sm" href="/pedidos/<?= (int) $pedido['numero'] ?>">Ver detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="mt-3"><?= $paginacao ?></div>

<?php include __DIR__ . '/../layouts/escola_footer.php';