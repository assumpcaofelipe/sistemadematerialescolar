<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ItemPedido
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $pedidoId, int $produtoId, string $produtoNome, int $quantidade): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO itens_pedido (pedido_id, produto_id, produto_nome, quantidade)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$pedidoId, $produtoId, $produtoNome, $quantidade]);
    }
}