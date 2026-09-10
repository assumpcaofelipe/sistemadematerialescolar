<?php $pageTitle = 'Confirme seu pedido'; include __DIR__ . '/../layouts/escola_header.php'; ?>

<h1 class="h4 mb-3">✅ Confirme seu pedido</h1>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Escola:</strong> <?= e($usuario['nome_escola'] ?? $usuario['nome']) ?></p>
        <h6 class="mt-3 mb-2">Produtos</h6>
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr><th>Produto</th><th class="text-center" style="width:100px;">Quantidade</th></tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= e($item['produto']['nome']) ?></td>
                        <td class="text-center"><?= (int) $item['quantidade'] ?>x</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
    <a href="/carrinho" class="btn btn-lg btn-outline-secondary">← Voltar e corrigir</a>
    <form method="post" action="/pedido/confirmar">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-lg btn-success">Confirmar pedido ✓</button>
    </form>
</div>

<?php include __DIR__ . '/../layouts/escola_footer.php';