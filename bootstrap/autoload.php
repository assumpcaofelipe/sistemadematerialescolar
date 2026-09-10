<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 para o namespace App\
 * Usa o autoload do Composer quando disponível.
 */

// Composer (vendor/autoload.php) — usado para PHPMailer e afins
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

// Funções globais auxiliares (slugify, e, csrf_field, ...)
require __DIR__ . '/../app/Core/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});