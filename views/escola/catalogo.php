<?php $pageTitle = 'Catálogo'; include __DIR__ . '/../layouts/escola_header.php'; ?>

<h1 class="h4 mb-3">Catálogo de materiais</h1>

<form method="get" action="/catalogo" class="row g-2 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" class="form-control form-control-lg" name="busca"
               placeholder="Buscar produto..." value="<?= e($busca) ?>">
    </div>
    <div class="col-8 col-md-4 col-lg-3">
        <select name="categoria" class="form-select form-select-lg">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int) $categoria['id'] ?>"
                    <?= ((int) $categoria['id'] === (int) $categoriaId) ? 'selected' : '' ?>>
                    <?= e($categoria['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-4 col-md-2 d-flex gap-2">
        <button class="btn btn-primary btn-lg flex-fill" type="submit">Buscar</button>
        <?php if ($busca !== '' || $categoriaId): ?>
            <a href="/catalogo" class="btn btn-lg btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<p class="text-muted small">
    <?= $total > 0 ? "Exibindo {$total} produto(s)." : 'Nenhum produto encontrado.' ?>
</p>

<div class="row g-3">
    <?php foreach ($produtos as $produto): ?>
        <?php $disponivel = (int) $produto['quantidade_estoque'] > 0; ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 card-produto shadow-sm <?= $disponivel ? '' : 'produto-indisponivel' ?>">
                <?php if ($produto['imagem']): ?>
                    <img src="<?= BASE_URL ?>/<?= e($produto['imagem']) ?>" class="card-img-top" alt="">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center text-muted" style="font-size:3rem;">
                        <?= icon('package', '48', '48') ?>
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1"><?= e($produto['nome']) ?></h5>
                    <p class="text-muted small mb-2"><?= e($produto['categoria_nome']) ?></p>
                    <p class="card-text small flex-grow-1"><?= e($produto['descricao']) ?></p>

                    <?php if ($disponivel): ?>
                        <form class="mt-2 js-adicionar-carrinho">
                            <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
                            <div class="input-group input-group-sm">
                                <input type="number" name="quantidade" min="1"
                                       max="<?= (int) $produto['quantidade_estoque'] ?>"
                                       value="1" class="form-control text-center">
                                <button type="submit" class="btn btn-primary">
                                    <span class="d-inline d-md-none">+</span>
                                    <span class="d-none d-md-inline">Adicionar</span>
                                </button>
                            </div>
                            <small class="text-muted">Disponível: <?= (int) $produto['quantidade_estoque'] ?></small>
                        </form>
                    <?php else: ?>
                        <div class="mt-2">
                            <span class="badge bg-secondary fs-6">Indisponível</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!$produtos): ?>
    <div class="text-center text-muted py-5">
        <p class="fs-4 mb-2">Nenhum produto encontrado</p>
        <p>Tente ajustar a busca ou o filtro de categoria.</p>
    </div>
<?php endif; ?>

<div class="mt-3"><?= $paginacao ?></div>

<?php include __DIR__ . '/../layouts/escola_footer.php';