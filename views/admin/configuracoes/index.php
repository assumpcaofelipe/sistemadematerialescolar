<?php $pageTitle = 'Configurações'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Configurações da secretaria</h2>
</div>

<form method="post" action="/admin/configuracoes" class="card shadow-sm" style="max-width: 680px;">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" for="whatsapp">Número de WhatsApp da secretaria</label>
            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                   placeholder="Ex: 5511998877665" value="<?= e($configuracoes['whatsapp_secretaria'] ?? '') ?>">
            <div class="form-text">
                Somente números, com DDI e DDD. Usado para montar o link wa.me.
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">E-mail da secretaria (recebe notificações)</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= e($configuracoes['email_secretaria'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Salvar configurações</button>
    </div>
</form>

<?php include __DIR__ . '/../../layouts/admin_footer.php';