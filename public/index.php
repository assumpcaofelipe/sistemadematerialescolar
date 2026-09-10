<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/autoload.php';
require __DIR__ . '/../config/config.php';

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