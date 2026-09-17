<?php $pageTitle = 'Pedido enviado'; include __DIR__ . '/../layouts/escola_header.php'; ?>

<div class="text-center py-4">
    <div class="display-1 mb-3 text-success"><?= icon('check-circle', '64', '64') ?></div>
    <h1 class="h3 mb-2">Pedido registrado com sucesso! ✅</h1>
    <p class="fs-4 mb-1">Seu pedido é o <strong>#<?= (int) $numero ?></strong>.</p>
    <p class="text-muted">
        O pedido foi registrado no sistema e um e-mail de aviso será enviado à Secretaria.
    </p>
    <p class="text-muted">
        Para confirmar o pedido, clique no botão abaixo e envie a mensagem pelo WhatsApp da Secretaria.
    </p>

    <a href="<?= e($linkWhats) ?>" target="_blank" rel="noopener" class="btn btn-success btn-lg mb-3">
        <?= icon('message-circle', '18', '18', 'me-1') ?>Enviar mensagem no WhatsApp
    </a>

    <div class="card shadow-sm mx-auto mt-2" style="max-width: 560px;">
        <div class="card-body text-start" style="white-space: pre-line;">
            <small class="text-muted d-block mb-2">Mensagem:</small>
            <?= e($mensagem) ?>
        </div>
    </div>

    <a href="/catalogo" class="btn btn-outline-primary btn-lg mt-4">Voltar ao catálogo</a>
</div>

<?php include __DIR__ . '/../layouts/escola_footer.php';