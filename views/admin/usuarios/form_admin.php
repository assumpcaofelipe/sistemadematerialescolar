<?php $pageTitle = 'Adicionar usuário do sistema'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Adicionar usuário do sistema</h2>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <form method="post" action="/admin/usuarios/admin/salvar" class="card shadow-sm">
            <div class="card-body">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="tipo">Tipo de usuário *</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                    <div class="form-text">
                        Administrador acessa tudo, inclusive usuários e escolas.
                        Supervisor não acessa o sistema de usuários nem cadastra escolas.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="nome">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required
                           placeholder="Nome do usuário">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">E-mail / login *</label>
                    <input type="email" class="form-control" id="email" name="email" required
                           autocomplete="off" placeholder="usuario@educacao.gov">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="senha">Senha *</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="senha" name="senha"
                               value="<?= e($senhaGerada) ?>" autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="btnGerarSenha">Gerar</button>
                    </div>
                    <div class="form-text">Sugestão gerada: <code><?= e($senhaGerada) ?></code></div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                        <label class="form-check-label" for="status">Ativo</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Salvar usuário</button>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Usuários do sistema</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>E-mail / login</th>
                            <th>Tipo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$usuarios): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($usuarios as $sistema): ?>
                            <tr>
                                <td><?= e($sistema['nome']) ?></td>
                                <td><?= e($sistema['email']) ?></td>
                                <td>
                                    <?php if ($sistema['tipo'] === 'admin'): ?>
                                        <span class="badge bg-primary">Administrador</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Supervisor</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $sistema['status'] === 1): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnGerarSenha');
    if (btn) btn.addEventListener('click', function () {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        var senha = '';
        for (var i = 0; i < 8; i++) {
            senha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('senha').value = senha;
    });
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php';