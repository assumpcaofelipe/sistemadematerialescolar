<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Configuracao
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $chave, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT valor FROM configuracoes WHERE chave = ?');
        $stmt->execute([$chave]);
        $valor = $stmt->fetchColumn();
        return ($valor === false) ? $default : (string) $valor;
    }

    public function all(): array
    {
        $result = [];
        foreach ($this->db->query('SELECT chave, valor FROM configuracoes') as $row) {
            $result[$row['chave']] = $row['valor'];
        }
        return $result;
    }

    public function set(string $chave, string $valor): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
        );
        $stmt->execute([$chave, $valor]);
    }
}