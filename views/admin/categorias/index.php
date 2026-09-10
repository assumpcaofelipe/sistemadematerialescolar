<?php $pageTitle = 'Categorias'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Categorias</h2>
    <a href="/admin/categorias/nova" class="btn btn-primary">+ Nova categoria</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Ordem</th>
                    <th>Produtos</th>
                    <th class="table-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$categorias): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= e($categoria['nome']) ?></td>
                        <td class="text-muted"><?= e($categoria['slug']) ?></td>
                        <td>
                            <?php if ((int) $categoria['status'] === 1): ?>
                                <span class="badge bg-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $categoria['ordem'] ?></td>
                        <td><?= (new \App\Models\Categoria())->countProdutos((int) $categoria['id']) ?></td>
                        <td>
                            <a href="/admin/categorias/editar/<?= (int) $categoria['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="post" action="/admin/categorias/excluir/<?= (int) $categoria['id'] ?>"
                                  class="d-inline" onsubmit="return confirm('Excluir esta categoria?');">
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

<?php
include __DIR__ . '/../../layouts/admin_footer.php';