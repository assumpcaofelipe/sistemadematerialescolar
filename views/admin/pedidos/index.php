<?php $pageTitle = 'Pedidos'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Pedidos <span class="text-muted fs-6">(<?= $total ?>)</span></h2>
</div>

<form method="get" action="/admin/pedidos" class="row g-2 mb-3">
    <div class="col-12 col-md-3">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Todos os status</option>
            <?php foreach (\App\Core\StatusPedido::ROTULOS as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>" <?= ($status === $valor) ? 'selected' : '' ?>>
                    <?= e($rotulo) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-4">
        <select name="escola" class="form-select" onchange="this.form.submit()">
            <option value="">Todas as escolas</option>
            <?php foreach ($escolas as $esc): ?>
                <option value="<?= (int) $esc['id'] ?>" <?= ((int) $esc['id'] === (int) $escolaId) ? 'selected' : '' ?>><?= e($esc['nome_escola']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($status !== '' || $escolaId): ?>
        <div class="col-auto">
            <a href="/admin/pedidos" class="btn btn-outline-secondary">Limpar</a>
        </div>
    <?php endif; ?>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Número</th>
                    <th>Escola</th>
                    <th class="text-center">Itens</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th class="table-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$pedidos): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum pedido encontrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><strong>#<?= (int) $pedido['numero'] ?></strong></td>
                        <td><?= e($pedido['nome_escola'] ?? '-') ?></td>
                        <td class="text-center"><?= (int) $pedido['total_itens'] ?></td>
                        <td>
                            <span class="badge text-bg-<?= e(\App\Core\StatusPedido::cor($pedido['status'])) ?>">
                                <?= icon(\App\Core\StatusPedido::icone($pedido['status']), '14', '14', 'me-1') ?><?= e(\App\Core\StatusPedido::rotulo($pedido['status'])) ?>
                            </span>
                        </td>
                        <td><?= e(date('d/m/Y H:i', strtotime($pedido['created_at']))) ?></td>
                        <td>
                            <a href="/admin/pedidos/<?= (int) $pedido['numero'] ?>" class="btn btn-sm btn-outline-primary">Ver pedido</a>
                            <form method="post" action="/admin/pedidos/excluir/<?= (int) $pedido['id'] ?>" class="d-inline"
                                  onsubmit="return confirm('Excluir o pedido #<?= (int) $pedido['numero'] ?> e restaurar o estoque dos itens?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?= $paginacao ?></div>

<?php include __DIR__ . '/../../layouts/admin_footer.php';