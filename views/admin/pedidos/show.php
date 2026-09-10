<?php
$pageTitle = 'Pedido #' . $pedido['numero'];
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Pedido #<?= (int) $pedido['numero'] ?></h2>
    <a href="/admin/pedidos" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <span class="badge text-bg-<?= e(\App\Core\StatusPedido::cor($pedido['status'])) ?>">
                    <?= icon(\App\Core\StatusPedido::icone($pedido['status']), '14', '14', 'me-1') ?><?= e(\App\Core\StatusPedido::rotulo($pedido['status'])) ?>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Escola:</strong> <?= e($pedido['nome_escola'] ?? $pedido['usuario_nome']) ?></p>
                <p class="mb-1"><strong>Data:</strong> <?= e(date('d/m/Y H:i', strtotime($pedido['created_at']))) ?></p>

                <h6 class="mt-4 mb-2">Itens do pedido</h6>
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

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Alterar status</div>
            <div class="card-body">
                <form method="post" action="/admin/pedidos/status/<?= (int) $pedido['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <select class="form-select" name="status" required>
                            <?php foreach (\App\Core\StatusPedido::ROTULOS as $valor => $rotulo): ?>
                                <option value="<?= e($valor) ?>" <?= ($pedido['status'] === $valor) ? 'selected' : '' ?>>
                                    <?= e($rotulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Atualizar status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php';