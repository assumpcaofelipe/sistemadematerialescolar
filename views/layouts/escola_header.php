<?php
$pageTitle = $pageTitle ?? 'Catálogo';
$rota = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';
$totalCarrinho = (int) ($escolaCarrinhoItens ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> | <?= e($pageTitle) ?></title>
    <meta name="description" content="Central de Pedidos Escolares — Solicite materiais de forma organizada.">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/icone.png">
    <meta name="theme-color" content="#023e7d">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <link rel="icon" href="<?= BASE_URL ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/icone-192.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icone-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="app-body">
<aside class="app-sidebar">
    <a class="sidebar-brand" href="/catalogo">
        <?= icon('book', '20', '20', 'me-2') ?><span>Central de Pedidos</span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu principal">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?= $rota === '/catalogo' ? ' active' : '' ?>" href="/catalogo">
                    <?= icon('grid', '18', '18', 'me-2') ?>Catálogo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/carrinho') ? ' active' : '' ?>" href="/carrinho" id="navCarrinho">
                    <?= icon('cart', '18', '18', 'me-2') ?><span>Carrinho</span>
                    <span class="badge text-bg-warning nav-carrinho-contador<?= $totalCarrinho > 0 ? '' : ' d-none' ?>" id="navCarrinhoContador"><?= $totalCarrinho ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/pedidos') ? ' active' : '' ?>" href="/pedidos">
                    <?= icon('inbox', '18', '18', 'me-2') ?>Histórico de pedidos
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-usuario text-truncate">
            <?= e($usuario['nome_escola'] ?? $usuario['nome'] ?? 'Escola') ?>
        </div>
        <a class="sidebar-logout" href="/logout">
            <?= icon('log-out', '18', '18', 'me-2') ?>Sair
        </a>
    </div>
</aside>

<div class="app-content">
<main class="container-fluid px-4 py-4">