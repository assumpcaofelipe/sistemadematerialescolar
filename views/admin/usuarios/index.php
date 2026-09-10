<?php $pageTitle = 'Escolas cadastradas'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0">Escolas cadastradas</h2>
    <a href="/admin/usuarios/novo" class="btn btn-primary">+ Nova escola</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Escola</th>
                    <th>E-mail / login</th>
                    <th>Status</th>
                    <th class="table-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$usuarios): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma escola cadastrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <div><?= e($usuario['nome_escola']) ?></div>
                        </td>
                        <td><?= e($usuario['email']) ?></td>
                        <td>
                            <?php if ((int) $usuario['status'] === 1): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/usuarios/editar/<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="post" action="/admin/usuarios/excluir/<?= (int) $usuario['id'] ?>"
                                  class="d-inline" onsubmit="return confirm('Excluir este usuário?');">
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

<?php include __DIR__ . '/../../layouts/admin_footer.php';