<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#023e7d">
    <title>404 — Não encontrado | <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/icone-192.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icone-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center bg-light" style="min-height:100vh;">
    <div class="container text-center">
        <h1 class="display-1 fw-bold text-primary">404</h1>
        <p class="fs-4">Página não encontrada.</p>
        <a href="/" class="btn btn-primary btn-lg">Voltar ao início</a>
    </div>
</body>
</html>