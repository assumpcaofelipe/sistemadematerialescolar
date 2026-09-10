<?php $pageTitle = 'Carrinho'; include __DIR__ . '/../layouts/escola_header.php'; ?>

<h1 class="h4 mb-3"><?= icon('cart', '20', '20', 'me-1') ?>Carrinho</h1>

<?php if (!$itens): ?>
    <div class="text-center text-muted py-5">
        <p class="fs-4 mb-2">Seu carrinho está vazio</p>
        <a href="/catalogo" class="btn btn-primary btn-lg">Ver catálogo</a>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th class="text-center" style="width:180px;">Quantidade</th>
                        <th class="text-center" style="width:90px;">Disponível</th>
                        <th class="table-acoes text-end">Remover</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <?php $produto = $item['produto']; ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= e($produto['nome']) ?></strong>
                                    <div class="small text-muted"><?= e($produto['descricao']) ?></div>
                                </div>
                            </td>
                            <td>
                                <form class="js-atualizar-carrinho d-flex justify-content-center gap-2">
                                    <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
                                    <input type="number" name="quantidade" min="0"
                                           max="<?= (int) $produto['quantidade_estoque'] ?>"
                                           value="<?= (int) $item['quantidade'] ?>"
                                           class="form-control form-control-sm text-center" style="width:80px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Atualizar</button>
                                </form>
                            </td>
                            <td class="text-center"><?= (int) $produto['quantidade_estoque'] ?></td>
                            <td class="text-end">
                                <form class="js-remover-carrinho">
                                    <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3">
        <a href="/catalogo" class="btn btn-lg btn-outline-primary">← Continuar escolhendo</a>
        <a href="/pedido/revisao" class="btn btn-lg btn-success">Finalizar pedido →</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/escola_footer.php';