<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/autoload.php';
require __DIR__ . '/../config/config.php';

// Impede que o navegador/PWA armazene páginas dinâmicas (evita versões antigas em cache)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

use App\Core\Session;
use App\Core\Router;

Session::start();

$routes = require __DIR__ . '/../routes/web.php';
$router = new Router();

foreach ($routes['GET'] ?? [] as $path => $handler) {
    $router->get($path, $handler);
}
foreach ($routes['POST'] ?? [] as $path => $handler) {
    $router->post($path, $handler);
}

$router->dispatch();