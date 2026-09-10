<?php

declare(strict_types=1);

namespace App\Core;

class Model
{
    protected \PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByPk(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        $orderBy = preg_replace('/[^a-zA-Z0-9_\s,]/', '', $orderBy);
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }
}