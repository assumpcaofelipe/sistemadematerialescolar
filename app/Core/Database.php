<?php

declare(strict_types=1);

namespace App\Core;

class Database
{
    private static ?\PDO $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $name = getenv('DB_NAME') ?: 'central_pedidos';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new \PDO($dsn, $user, $pass, $options);
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
