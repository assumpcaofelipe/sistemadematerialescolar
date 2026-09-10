<?php $pageTitle = 'Login Administrativo'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#023e7d">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/icone-192.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icone-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-login-admin d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="text-center mb-4 text-white">
                    <h1 class="h3 mb-1">Painel Administrativo</h1>
                    <p class="text-white-50 mb-0">Central de Pedidos Escolares</p>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form method="post" action="/admin/login">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label" for="email">E-mail</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                       required autofocus autocomplete="username">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="senha">Senha</label>
                                <input type="password" class="form-control form-control-lg" id="senha" name="senha"
                                       required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Entrar</button>
                        </form>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link btn-sm link-recuperar"
                                    data-bs-toggle="modal" data-bs-target="#modalRecuperarSenha">
                                Esqueci a senha
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-center text-white-50 small mt-3 mb-0">
                    Acesso restrito à secretaria.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal recuperar senha -->
    <div class="modal fade" id="modalRecuperarSenha" tabindex="-1" aria-labelledby="modalRecuperarSenhaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRecuperarSenhaLabel">Recuperar senha</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Digite seu e-mail cadastrado e a nova senha desejada.</p>
                    <div class="alert d-none" id="msFeedback" role="alert"></div>
                    <form id="formRecuperarSenha">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="recEmail">E-mail</label>
                            <input type="email" class="form-control" id="recEmail" name="email" required autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="recSenha">Nova senha</label>
                            <input type="password" class="form-control" id="recSenha" name="senha" required minlength="4" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="recSenha2">Repetir nova senha</label>
                            <input type="password" class="form-control" id="recSenha2" name="senha_confirmar" required minlength="4" autocomplete="new-password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnRecuperar" form="formRecuperarSenha">Criar nova senha</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formRecuperarSenha');
    var btn = document.getElementById('btnRecuperar');
    var feedback = document.getElementById('msFeedback');

    function mostrar(tipo, msg) {
        feedback.className = 'alert alert-' + tipo + ' mb-0';
        feedback.textContent = msg;
        feedback.classList.remove('d-none');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var senha = document.getElementById('recSenha').value;
        var senha2 = document.getElementById('recSenha2').value;
        if (senha !== senha2) {
            mostrar('danger', 'As senhas não conferem.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Salvando...';

        var fd = new FormData(form);
        fetch('/admin/recuperar-senha', { method: 'POST', body: fd })
            .then(function (resp) {
                return resp.json().then(function (data) {
                    if (!resp.ok) throw data;
                    return data;
                });
            })
            .then(function (data) {
                mostrar('success', data.mensagem);
                form.reset();
            })
            .catch(function (err) {
                mostrar('danger', (err && err.erro) ? err.erro : 'Falha ao recuperar senha.');
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = 'Criar nova senha';
            });
    });

    document.getElementById('modalRecuperarSenha')
        .addEventListener('hidden.bs.modal', function () {
            feedback.className = 'alert d-none';
            feedback.textContent = '';
            form.reset();
        });
});
</script>
<?php include __DIR__ . '/../layouts/alertas.php'; ?>
</body>
</html>