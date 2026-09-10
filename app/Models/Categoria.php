<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Categoria
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(bool $somenteAtivas = false): array
    {
        $sql = 'SELECT * FROM categorias';
        if ($somenteAtivas) {
            $sql .= ' WHERE status = 1';
        }
        $sql .= ' ORDER BY ordem ASC, nome ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorias (nome, slug, status, ordem) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nome'],
            $data['slug'],
            $data['status'] ?? 1,
            $data['ordem'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorias SET nome = ?, slug = ?, status = ?, ordem = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['nome'],
            $data['slug'],
            $data['status'] ?? 1,
            $data['ordem'] ?? 0,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countProdutos(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM produtos WHERE categoria_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}