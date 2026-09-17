<?php $pageTitle = 'Estoque'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0"><?= icon('alert-triangle', '20', '20', 'me-1') ?>Estoque <span class="text-muted fs-6">(<?= $total ?>)</span></h2>
</div>
<p class="text-muted small">Consulta apenas. Para alterar a quantidade ou o status, use <a href="/admin/produtos">Produtos</a> &rarr; Novo/Editar.</p>

<form method="get" action="/admin/estoque" class="row g-2 mb-3">
    <div class="col-12 col-md-4">
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
    <div class="col-6 col-md-3">
        <select name="situacao" class="form-select">
            <option value="todos" <?= ($situacao === 'todos') ? 'selected' : '' ?>>Todos os produtos (maior para o menor)</option>
            <option value="asc" <?= ($situacao === 'asc') ? 'selected' : '' ?>>Todos os produtos (menor para o maior)</option>
            <option value="baixo" <?= ($situacao === 'baixo') ? 'selected' : '' ?>>Estoque baixo (menor que <?= (int) $limiteBaixo ?>)</option>
            <option value="zerado" <?= ($situacao === 'zerado') ? 'selected' : '' ?>>Estoque zerado</option>
        </select>
    </div>
    <div class="col-12 col-md-2 d-flex gap-2">
        <button class="btn btn-primary flex-fill" type="submit">Filtrar</button>
        <?php if ($busca !== '' || $categoriaId || $situacao !== 'todos'): ?>
            <a href="/admin/estoque" class="btn btn-outline-secondary">Limpar</a>
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
                </tr>
            </thead>
            <tbody>
                <?php if (!$produtos): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Nenhum produto nesta condição.</td></tr>
                <?php endif; ?>
                <?php foreach ($produtos as $produto): ?>
                    <?php $qtd = (int) $produto['quantidade_estoque']; ?>
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
                            <input type="number" step="1" disabled
                                   class="form-control form-control-sm text-center d-inline-block"
                                   style="width: 90px; color: <?= $qtd < 0 ? '#dc3545' : 'inherit' ?>;"
                                   value="<?= $qtd ?>"<?= $qtd < 0 ? ' title="Estoque em falta"' : '' ?>>
                        </td>
                        <td class="text-center">
                            <select class="form-select form-select-sm d-inline-block" style="width: 110px;" disabled>
                                <option value="1" <?= ((int) $produto['status'] === 1) ? 'selected' : '' ?>>Ativo</option>
                                <option value="0" <?= ((int) $produto['status'] === 0) ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?= $paginacao ?></div>

<?php include __DIR__ . '/../../layouts/admin_footer.php';