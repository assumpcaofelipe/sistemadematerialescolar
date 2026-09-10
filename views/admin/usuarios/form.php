<?php
$pageTitle = $usuario ? 'Editar escola' : 'Nova escola';
include __DIR__ . '/../../layouts/admin_header.php';
$url = $usuario
    ? '/admin/usuarios/atualizar/' . (int) $usuario['id']
    : '/admin/usuarios/salvar';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0"><?= e($pageTitle) ?></h2>
    <a href="/admin/usuarios" class="btn btn-outline-secondary btn-sm">&laquo; Voltar</a>
</div>

<form method="post" action="<?= e($url) ?>" class="card shadow-sm" style="max-width: 680px;">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" for="nome_escola">Nome da escola *</label>
            <input type="text" class="form-control" id="nome_escola" name="nome_escola" required
                   value="<?= e($usuario['nome_escola'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">E-mail / login *</label>
            <input type="email" class="form-control" id="email" name="email" required
                   value="<?= e($usuario['email'] ?? '') ?>" autocomplete="off">
        </div>

        <div class="mb-3">
            <label class="form-label" for="senha">
                Senha <?= $usuario ? '(deixe em branco para não alterar)' : '*' ?>
            </label>
            <div class="input-group">
                <input type="text" class="form-control" id="senha" name="senha"
                       value="<?= $usuario ? '' : e($senhaGerada) ?>" autocomplete="off">
                <button type="button" class="btn btn-outline-secondary" id="btnGerarSenha">Gerar</button>
            </div>
            <div class="form-text">Sugestão gerada: <code><?= e($senhaGerada) ?></code></div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<script>
document.getElementById('btnGerarSenha').addEventListener('click', function () {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    var senha = '';
    for (var i = 0; i < 8; i++) {
        senha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('senha').value = senha;
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php';