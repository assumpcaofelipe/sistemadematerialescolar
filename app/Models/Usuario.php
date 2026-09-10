<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Usuario
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(string $tipo = 'escola'): array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE tipo = ? ORDER BY nome');
        $stmt->execute([$tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Usuários do painel administrativo (admins + supervisores).
     */
    public function allSistema(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios
             WHERE tipo IN ('admin','supervisor')
             ORDER BY FIELD(tipo, 'admin', 'supervisor'), nome"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, senha, tipo, nome_escola, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nome'],
            $data['email'],
            $data['senha'],
            $data['tipo'],
            $data['nome_escola'] ?? null,
            $data['status'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $senhaSql = '';
        $values = [];

        if (!empty($data['senha'])) {
            $senhaSql = ', senha = ?';
            $values[] = $data['senha'];
        }

        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET nome = ?, email = ?, nome_escola = ?, status = ?' . $senhaSql . '
             WHERE id = ?'
        );
        $values = array_merge([
            $data['nome'],
            $data['email'],
            $data['nome_escola'] ?? null,
            $data['status'] ?? 1,
        ], $values);
        $values[] = $id;

        return $stmt->execute($values);
    }

    public function updateSenha(int $id, string $senhaHash): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        return $stmt->execute([$senhaHash, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countByTipo(string $tipo): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE tipo = ?');
        $stmt->execute([$tipo]);
        return (int) $stmt->fetchColumn();
    }
}