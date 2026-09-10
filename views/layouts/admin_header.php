<?php
$pageTitle = $pageTitle ?? 'Painel Administrativo';
$rota = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';
$configAberto = str_starts_with($rota, '/admin/usuarios/novo') || str_starts_with($rota, '/admin/usuarios/admin');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#023e7d">
    <title><?= e(APP_NAME) ?> — Admin | <?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/icone-192.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icone-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="app-body">
<aside class="app-sidebar">
    <a class="sidebar-brand" href="/admin/dashboard">
        <?= icon('wrench', '20', '20', 'me-2') ?><span>Central de Pedidos</span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu administrativo">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/dashboard') ? ' active' : '' ?>" href="/admin/dashboard">
                    <?= icon('grid', '18', '18', 'me-2') ?>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/pedidos') ? ' active' : '' ?>" href="/admin/pedidos">
                    <?= icon('inbox', '18', '18', 'me-2') ?>Novos pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/produtos') ? ' active' : '' ?>" href="/admin/produtos">
                    <?= icon('package', '18', '18', 'me-2') ?>Produtos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/estoque') ? ' active' : '' ?>" href="/admin/estoque">
                    <?= icon('alert-triangle', '18', '18', 'me-2') ?>Estoque
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/categorias') ? ' active' : '' ?>" href="/admin/categorias">
                    <?= icon('tag', '18', '18', 'me-2') ?>Categorias
                </a>
            </li>
            <?php if (\App\Core\Auth::isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link<?= str_starts_with($rota, '/admin/usuarios') && !$configAberto ? ' active' : '' ?>" href="/admin/usuarios">
                    <?= icon('school', '18', '18', 'me-2') ?>Escolas
                </a>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link sidebar-submenu-toggle<?= $configAberto ? ' expanded' : '' ?>" data-target="#submenuConfiguracoes" aria-expanded="<?= $configAberto ? 'true' : 'false' ?>">
                    <?= icon('settings', '18', '18', 'me-2') ?><span>Configurações</span>
                    <span class="sidebar-chevron ms-auto"><?= icon('chevron-down', '16', '16') ?></span>
                </button>
                <ul class="sidebar-submenu<?= $configAberto ? ' aberto' : '' ?>" id="submenuConfiguracoes">
                    <li>
                        <a class="<?= $rota === '/admin/usuarios/novo' ? 'active' : '' ?>" href="/admin/usuarios/novo">
                            <?= icon('user-plus', '16', '16', 'me-2') ?>Adicionar escolas
                        </a>
                    </li>
                    <li>
                        <a class="<?= str_starts_with($rota, '/admin/usuarios/admin') ? 'active' : '' ?>" href="/admin/usuarios/admin/novo">
                            <?= icon('user-shield', '16', '16', 'me-2') ?>Adicionar usuários do sistema
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-usuario text-truncate" title="<?= e(\App\Core\Auth::user()['nome']) ?>">
            Olá, <?= e(\App\Core\Auth::user()['nome']) ?>
        </div>
        <a class="sidebar-logout" href="/admin/logout">
            <?= icon('log-out', '18', '18', 'me-2') ?>Sair
        </a>
    </div>
</aside>

<div class="app-content">
<main class="container-fluid px-4 py-4">