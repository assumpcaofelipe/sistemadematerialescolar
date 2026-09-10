<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Produto
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(bool $somenteAtivos = false): array
    {
        $sql = 'SELECT p.*, c.nome AS categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON c.id = p.categoria_id';
        if ($somenteAtivos) {
            $sql .= ' WHERE p.status = 1';
        }
        $sql .= ' ORDER BY p.nome ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lista produtos com paginação, busca por nome/descricao
     * e filtro por categoria.
     *
     * @return array{items: array, total: int}
     */
    public function paginar(
        ?string $busca = null,
        ?int $categoriaId = null,
        int $pagina = 1,
        int $porPagina = 20,
        bool $somenteAtivos = false
    ): array {
        $condicoes = [];
        $params = [];

        if ($somenteAtivos) {
            $condicoes[] = 'p.status = 1 AND c.status = 1';
        }

        if ($busca !== null && $busca !== '') {
            $condicoes[] = '(p.nome LIKE ? OR p.descricao LIKE ?)';
            $like = '%' . $busca . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($categoriaId)) {
            $condicoes[] = 'p.categoria_id = ?';
            $params[] = (int) $categoriaId;
        }

        $where = $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id{$where}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $deslocamento = max(0, ($pagina - 1) * $porPagina);
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id{$where}
             ORDER BY p.nome ASC
             LIMIT {$porPagina} OFFSET {$deslocamento}"
        );
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public function catalog(?string $busca = null, ?int $categoriaId = null): array
    {
        return $this->paginar($busca, $categoriaId, 1, 100000, true)['items'];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca vários produtos em uma única consulta, preservando a
     * ordem dos ids informados.
     *
     * @return array<int, array>
     */
    public function findMany(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id IN ({$marcadores})"
        );
        $stmt->execute($ids);

        $porId = [];
        foreach ($stmt->fetchAll() as $linha) {
            $porId[(int) $linha['id']] = $linha;
        }

        $resultado = [];
        foreach ($ids as $id) {
            if (isset($porId[$id])) {
                $resultado[] = $porId[$id];
            }
        }

        return $resultado;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM produtos WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO produtos
             (categoria_id, nome, slug, descricao, imagem, quantidade_estoque, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['categoria_id'],
            $data['nome'],
            $data['slug'],
            $data['descricao'],
            $data['imagem'] ?? null,
            $data['quantidade_estoque'] ?? 0,
            $data['status'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE produtos
             SET categoria_id = ?, nome = ?, slug = ?, descricao = ?,
                 imagem = ?, quantidade_estoque = ?, status = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $data['categoria_id'],
            $data['nome'],
            $data['slug'],
            $data['descricao'],
            $data['imagem'] ?? null,
            $data['quantidade_estoque'] ?? 0,
            $data['status'] ?? 1,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM produtos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function ajustarEstoque(int $id, int $quantidade): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE produtos SET quantidade_estoque = ? WHERE id = ?'
        );
        return $stmt->execute([$quantidade, $id]);
    }

    public function debitarEstoque(int $id, int $quantidade): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE produtos
             SET quantidade_estoque = quantidade_estoque - ?
             WHERE id = ? AND quantidade_estoque >= ?'
        );
        return $stmt->execute([$quantidade, $id, $quantidade]);
    }

    public function baixoEstoque(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id
             WHERE p.status = 1 AND p.quantidade_estoque <= ?
             ORDER BY p.quantidade_estoque ASC'
        );
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }

    /**
     * Produtos com estoque baixo/zerado, com busca, filtro por
     * categoria/situação e paginação.
     *
     * @return array{items: array, total: int}
     */
    public function paginarEstoque(
        ?string $busca = null,
        ?int $categoriaId = null,
        string $situacao = '',
        int $limiteBaixo = 5,
        int $pagina = 1,
        int $porPagina = 20
    ): array {
        $condicoes = ['p.status = 1'];
        $params = [];

        if ($situacao === 'zerado') {
            $condicoes[] = 'p.quantidade_estoque = 0';
        } elseif ($situacao === 'baixo') {
            $condicoes[] = 'p.quantidade_estoque BETWEEN 1 AND ?';
            $params[] = $limiteBaixo;
        } else {
            $condicoes[] = 'p.quantidade_estoque <= ?';
            $params[] = $limiteBaixo;
        }

        if ($busca !== null && $busca !== '') {
            $condicoes[] = '(p.nome LIKE ? OR p.descricao LIKE ?)';
            $like = '%' . $busca . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($categoriaId)) {
            $condicoes[] = 'p.categoria_id = ?';
            $params[] = (int) $categoriaId;
        }

        $where = ' WHERE ' . implode(' AND ', $condicoes);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id{$where}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $deslocamento = max(0, ($pagina - 1) * $porPagina);
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             INNER JOIN categorias c ON c.id = p.categoria_id{$where}
             ORDER BY p.quantidade_estoque ASC, p.nome ASC
             LIMIT {$porPagina} OFFSET {$deslocamento}"
        );
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public function maisPedidos(
        int $limite = 10,
        int $dias = 0,
        ?string $status = null,
        ?int $escolaId = null,
        ?int $categoriaId = null
    ): array {
        $condicoes = ["pe.status != 'cancelado'"];
        $params = [];
        if ($dias > 0) {
            $condicoes[] = 'pe.created_at >= ?';
            $params[] = date('Y-m-d', strtotime('-' . ($dias - 1) . ' days')) . ' 00:00:00';
        }
        if ($status !== null && $status !== '') {
            $condicoes[] = 'pe.status = ?';
            $params[] = $status;
        }
        if ($escolaId) {
            $condicoes[] = 'pe.usuario_id = ?';
            $params[] = (int) $escolaId;
        }
        if ($categoriaId) {
            $condicoes[] = 'p.categoria_id = ?';
            $params[] = (int) $categoriaId;
        }
        $where = implode(' AND ', $condicoes);

        $stmt = $this->db->prepare(
            'SELECT ip.produto_nome AS nome, SUM(ip.quantidade) AS total
             FROM itens_pedido ip
             INNER JOIN pedidos pe ON pe.id = ip.pedido_id
             LEFT JOIN produtos p ON p.id = ip.produto_id
             WHERE ' . $where . '
             GROUP BY ip.produto_nome
             ORDER BY total DESC
             LIMIT ?'
        );
        foreach ($params as $i => $p) {
            $stmt->bindValue($i + 1, $p);
        }
        $stmt->bindValue(count($params) + 1, $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}