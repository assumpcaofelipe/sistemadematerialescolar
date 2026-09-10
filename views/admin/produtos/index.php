<?php $pageTitle = 'Produtos'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Produtos <span class="text-muted fs-6">(<?= $total ?>)</span></h2>
    <a href="/admin/produtos/novo" class="btn btn-primary">+ Novo produto</a>
</div>

<form method="get" action="/admin/produtos" class="row g-2 mb-3">
    <div class="col-12 col-md-5">
        <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou descrição..."
               value="<?= e($busca) ?>">
    </div>
    <div class="col-6 col-md-3">
        <select name="categoria" class="form-select">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int) $categoria['id'] ?>"
                    <?= ((int) $categoria['id'] === (int) $categoriaId) ? 'selected' : '' ?>>
                    <?= e($categoria['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill" type="submit">Filtrar</button>
        <?php if ($busca !== '' || $categoriaId): ?>
            <a href="/admin/produtos" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th class="text-center">Estoque</th>
                    <th class="text-center">Status</th>
                    <th class="table-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$produtos): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="max-width: 520px;">
                                <?php if ($produto['imagem']): ?>
                                    <img src="<?= BASE_URL ?>/<?= e($produto['imagem']) ?>"
                                         class="rounded" width="42" height="42" style="object-fit: cover;" alt="">
                                <?php else: ?>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted"
                                          style="width:42px;height:42px;"><?= icon('package', '20', '20') ?></span>
                                <?php endif; ?>
                                <div>
                                    <div class="text-truncate" title="<?= e($produto['nome']) ?>"><?= e($produto['nome']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= e($produto['categoria_nome']) ?></td>
                        <td class="text-center">
                            <form method="post" action="/admin/produtos/estoque/<?= (int) $produto['id'] ?>"
                                  class="d-inline-flex align-items-center gap-1 js-ajuste-estoque">
                                <?= csrf_field() ?>
                                <input type="number" name="quantidade" min="0" step="1"
                                       class="form-control form-control-sm text-center"
                                       style="width: 84px;" value="<?= (int) $produto['quantidade_estoque'] ?>">
                                <button class="btn btn-sm btn-outline-primary" type="submit" title="Salvar estoque">OK</button>
                            </form>
                        </td>
                        <td class="text-center">
                            <?php if ((int) $produto['status'] === 1): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/produtos/editar/<?= (int) $produto['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="post" action="/admin/produtos/excluir/<?= (int) $produto['id'] ?>"
                                  class="d-inline" onsubmit="return confirm('Excluir este produto?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
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