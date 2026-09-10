<?php

declare(strict_types=1);

// Carregamento do .env (varredura simples, sem dependências)
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

define('APP_NAME', 'Central de Pedidos Escolares');

// Detecção automática da base URL (funciona em subpasta e em vhosts)
if (!getenv('APP_URL')) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $protocol = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    // Sobe até a raiz do projeto (remove /public)
    $baseDir = preg_replace('#/public$#', '', $scriptDir);
    define('BASE_URL', $protocol . '://' . $host . $baseDir);
} else {
    define('BASE_URL', rtrim(getenv('APP_URL'), '/'));
}

define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo');

date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding('UTF-8');