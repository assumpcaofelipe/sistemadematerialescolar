<?php $pageTitle = 'Pedido #' . $pedido['numero']; include __DIR__ . '/../layouts/escola_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="h4 mb-0">Pedido #<?= (int) $pedido['numero'] ?></h1>
    <a href="/pedidos" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Itens do pedido</h6>
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr><th>Produto</th><th class="text-center" style="width:100px;">Qtd</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= e($item['produto_nome']) ?></td>
                                <td class="text-center"><?= (int) $item['quantidade'] ?>x</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Acompanhamento</div>
            <div class="card-body">
                <p class="mb-2">
                    <span class="badge text-bg-<?= e(\App\Core\StatusPedido::cor($pedido['status'])) ?> fs-6">
                        <?= icon(\App\Core\StatusPedido::icone($pedido['status']), '16', '16', 'me-1') ?><?= e(\App\Core\StatusPedido::rotulo($pedido['status'])) ?>
                    </span>
                </p>
                <p class="mb-1 small text-muted"><strong>Realizado em:</strong><br><?= e(date('d/m/Y H:i', strtotime($pedido['created_at']))) ?></p>
                <p class="mb-1 small text-muted"><strong>Última atualização:</strong><br><?= e(date('d/m/Y H:i', strtotime($pedido['updated_at']))) ?></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/escola_footer.php';