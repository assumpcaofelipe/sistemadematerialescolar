<?php
$editando = !empty($usuario);
$pageTitle = $editando ? 'Editar usuário do sistema' : 'Adicionar usuário do sistema';
include __DIR__ . '/../../layouts/admin_header.php';
$url = $url ?? ($editando
    ? '/admin/usuarios/admin/atualizar/' . (int) $usuario['id']
    : '/admin/usuarios/admin/salvar');
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0"><?= e($pageTitle) ?></h2>
    <div class="d-flex gap-2">
        <a href="/admin/usuarios/admin/novo" class="btn btn-primary btn-sm">+ Novo usuário</a>
        <a href="/admin/usuarios" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
    </div>
</div>

<div class="card shadow-sm mb-4" style="max-width: 760px;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span>Dados do usuário</span>
        <?php if ($editando && (int) $usuario['id'] !== \App\Core\Auth::id()): ?>
            <form method="post" action="/admin/usuarios/admin/excluir/<?= (int) $usuario['id'] ?>"
                  class="d-inline" onsubmit="return confirm('Excluir este usuário do sistema?');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger" type="submit">Excluir usuário</button>
            </form>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= e($url) ?>">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="tipo">Tipo de usuário *</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="admin" <?= ($usuario['tipo'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="supervisor" <?= ($usuario['tipo'] ?? '') === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <div class="form-check form-switch" style="margin-top: 38px;">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                               <?= ($usuario['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Ativo</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-text mb-3">
            Administrador acessa tudo, inclusive usuários e escolas.
            Supervisor não acessa o sistema de usuários nem cadastra escolas.
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="nome">Nome *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required
                           placeholder="Nome do usuário" value="<?= e($usuario['nome'] ?? '') ?>">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail / login *</label>
                    <input type="email" class="form-control" id="email" name="email" required
                           autocomplete="off" placeholder="usuario@educacao.gov"
                           value="<?= e($usuario['email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="senha">
                Senha <?= $editando ? '(deixe em branco para não alterar)' : '*' ?>
            </label>
            <div class="input-group" style="max-width: 420px;">
                <input type="text" class="form-control" id="senha" name="senha"
                       value="<?= $editando ? '' : e($senhaGerada) ?>" autocomplete="off">
                <button type="button" class="btn btn-outline-secondary" id="btnGerarSenha">Gerar</button>
            </div>
            <div class="form-text">Sugestão gerada: <code><?= e($senhaGerada) ?></code></div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar usuário</button>
    </div>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span>Usuários do sistema</span>
        <span class="text-muted small"><?= count($usuarios) ?> usuário(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>E-mail / login</th>
                    <th>Função</th>
                    <th>Status</th>
                    <th class="table-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$usuarios): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($usuarios as $sistema): ?>
                    <tr>
                        <td>
                            <div><?= e($sistema['nome']) ?></div>
                            <?php if ((int) $sistema['id'] === \App\Core\Auth::id()): ?>
                                <span class="badge bg-warning text-dark">Você</span>
                            <?php endif; ?>
                        </td>
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
                        <td>
                            <a href="/admin/usuarios/admin/editar/<?= (int) $sistema['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <?php if ((int) $sistema['id'] !== \App\Core\Auth::id()): ?>
                                <form method="post" action="/admin/usuarios/admin/excluir/<?= (int) $sistema['id'] ?>"
                                      class="d-inline" onsubmit="return confirm('Excluir este usuário do sistema?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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