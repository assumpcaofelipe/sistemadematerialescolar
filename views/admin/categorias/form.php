<?php
$pageTitle = $categoria ? 'Editar categoria' : 'Nova categoria';
include __DIR__ . '/../../layouts/admin_header.php';
$url = $categoria
    ? '/admin/categorias/atualizar/' . (int) $categoria['id']
    : '/admin/categorias/salvar';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><?= e($pageTitle) ?></h2>
    <a href="/admin/categorias" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
</div>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="<?= e($url) ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="nome">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" required
                       value="<?= e($categoria['nome'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label" for="slug">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug"
                       value="<?= e($categoria['slug'] ?? '') ?>">
                <div class="form-text">Deixe em branco para gerar automaticamente.</div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label" for="ordem">Ordem</label>
                    <input type="number" class="form-control" id="ordem" name="ordem" min="0"
                           value="<?= (int) ($categoria['ordem'] ?? 0) ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                            <?= ((int) ($categoria['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Ativa</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php';