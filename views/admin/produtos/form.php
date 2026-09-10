<?php
$pageTitle = $produto ? 'Editar produto' : 'Novo produto';
include __DIR__ . '/../../layouts/admin_header.php';
$url = $produto
    ? '/admin/produtos/atualizar/' . (int) $produto['id']
    : '/admin/produtos/salvar';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><?= e($pageTitle) ?></h2>
    <a href="/admin/produtos" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
</div>

<form method="post" action="<?= e($url) ?>" enctype="multipart/form-data" class="card shadow-sm" style="max-width: 760px;">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-12 col-md-8 mb-3">
                <label class="form-label" for="nome">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" required
                       value="<?= e($produto['nome'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-4 mb-3">
                <label class="form-label" for="categoria_id">Categoria *</label>
                <select class="form-select" id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>"
                            <?= ((int) ($produto['categoria_id'] ?? 0) === (int) $categoria['id']) ? 'selected' : '' ?>>
                            <?= e($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="descricao">Descrição *</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?= e($produto['descricao'] ?? '') ?></textarea>
            <div class="form-text">Inclua unidade/medida quando aplicável (ex: "UN", "CX c/ 50 un", "500g").</div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label" for="quantidade_estoque">Estoque</label>
                <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" min="0"
                       value="<?= (int) ($produto['quantidade_estoque'] ?? 0) ?>">
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                        <?= ((int) ($produto['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status">Ativo</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="slug">Slug</label>
            <input type="text" class="form-control" id="slug" name="slug"
                   value="<?= e($produto['slug'] ?? '') ?>">
            <div class="form-text">Deixe em branco para gerar automaticamente.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="imagem">Imagem (opcional, máx 2MB)</label>
            <?php if (!empty($produto['imagem'])): ?>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="<?= BASE_URL ?>/<?= e($produto['imagem']) ?>" width="96" height="96"
                         style="object-fit: cover;" class="rounded border" alt="Imagem atual">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remover_imagem" name="remover_imagem" value="1">
                        <label class="form-check-label text-danger" for="remover_imagem">Remover imagem atual</label>
                    </div>
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*">
            <div class="form-text">
                Sem arquivo = mantém a imagem atual. Opcional: o produto pode ficar sem foto.
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<?php include __DIR__ . '/../../layouts/admin_footer.php';