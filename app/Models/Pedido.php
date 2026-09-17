<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Models\Produto;

class Pedido
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT pe.*, u.nome AS usuario_nome, u.nome_escola
             FROM pedidos pe
             INNER JOIN usuarios u ON u.id = pe.usuario_id
             WHERE pe.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByNumero(int $numero): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT pe.*, u.nome AS usuario_nome, u.nome_escola
             FROM pedidos pe
             INNER JOIN usuarios u ON u.id = pe.usuario_id
             WHERE pe.numero = ?'
        );
        $stmt->execute([$numero]);
        return $stmt->fetch() ?: null;
    }

    public function itens(int $pedidoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM itens_pedido WHERE pedido_id = ?'
        );
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica se há estoque suficiente (e não zerado) para todos os
     * itens do pedido.
     *
     * @return array<int, string> Lista de problemas (vazia = ok)
     */
    public function problemasDeEstoqueParaConclusao(int $pedidoId): array
    {
        $itens = $this->itens($pedidoId);
        if (!$itens) {
            return ['O pedido não possui itens.'];
        }

        $produtoModel = new Produto();
        $problemas = [];

        foreach ($itens as $item) {
            $qtd = (int) $item['quantidade'];
            $nome = (string) $item['produto_nome'];

            $produto = $produtoModel->find((int) $item['produto_id']);
            $estoque = $produto ? (int) $produto['quantidade_estoque'] : 0;

            if (!$produto) {
                $problemas[] = "{$nome}: produto não encontrado.";
            } elseif ($estoque <= 0) {
                $problemas[] = "{$nome}: estoque zerado. Atualize o estoque para concluir o pedido.";
            } elseif ($qtd > $estoque) {
                $problemas[] = "{$nome}: estoque insuficiente (pedido: {$qtd}, disponível: {$estoque}).";
            }
        }

        return $problemas;
    }

    public function all(array $filtros = []): array
    {
        return $this->paginar($filtros, 1, 100000)['items'];
    }

    public function paginar(array $filtros = [], int $pagina = 1, int $porPagina = 20): array
    {
        $condicoes = [];
        $params = [];

        if (!empty($filtros['status'])) {
            $condicoes[] = 'pe.status = ?';
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['escola_id'])) {
            $condicoes[] = 'pe.usuario_id = ?';
            $params[] = (int) $filtros['escola_id'];
        }

        $where = $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '';
        $joins = 'INNER JOIN usuarios u ON u.id = pe.usuario_id';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM pedidos pe{$where}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $deslocamento = max(0, ($pagina - 1) * $porPagina);

        $stmt = $this->db->prepare(
            "SELECT pe.*, u.nome_escola,
                    (SELECT COUNT(*) FROM itens_pedido ip WHERE ip.pedido_id = pe.id) AS total_itens
             FROM pedidos pe
             {$joins}{$where}
             ORDER BY pe.id DESC
             LIMIT {$porPagina} OFFSET {$deslocamento}"
        );
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public function ultimoNumero(): int
    {
        $stmt = $this->db->query('SELECT COALESCE(MAX(numero), 2047) FROM pedidos');
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pedidos (numero, usuario_id, status) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $data['numero'],
            $data['usuario_id'],
            $data['status'] ?? 'realizado',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public function debitarItensEstoque(int $pedidoId): bool
    {
        return $this->movimentarItensEstoque($pedidoId, -1);
    }

    public function restaurarItensEstoque(int $pedidoId): bool
    {
        return $this->movimentarItensEstoque($pedidoId, +1);
    }

    private function movimentarItensEstoque(int $pedidoId, int $sinal): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $this->movimentarSemTransacao($pedidoId, $sinal);
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[Pedido] Falha ao movimentar estoque: ' . $e->getMessage());
            return false;
        }
    }

    private function movimentarSemTransacao(int $pedidoId, int $sinal): void
    {
        $itens = $this->itens($pedidoId);
        $stmtRestaurar = $this->db->prepare(
            'UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? WHERE id = ?'
        );
        $stmtDebitar = $this->db->prepare(
            'UPDATE produtos SET quantidade_estoque = quantidade_estoque - ?
             WHERE id = ? AND quantidade_estoque >= ?'
        );

        foreach ($itens as $item) {
            $qtd = (int) $item['quantidade'];
            $produtoId = (int) $item['produto_id'];

            if ($sinal < 0) {
                $stmtDebitar->execute([$qtd, $produtoId, $qtd]);
                if ($stmtDebitar->rowCount() === 0) {
                    throw new \RuntimeException("Estoque insuficiente para o produto #{$produtoId}");
                }
            } else {
                $stmtRestaurar->execute([$qtd, $produtoId]);
            }
        }
    }

    /**
     * Exclui o pedido em transação. O estoque é devolvido apenas se
     * o pedido já tiver dado baixa (status = concluido).
     */
    public function excluirComRestauro(int $id): bool
    {
        $pedido = $this->find($id);
        $restaurar = $pedido !== null && $pedido['status'] === 'concluido';

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            if ($restaurar) {
                $this->movimentarSemTransacao($id, +1);
            }

            $stmt = $this->db->prepare('DELETE FROM pedidos WHERE id = ?');
            $stmt->execute([$id]);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[Pedido] Falha ao excluir pedido: ' . $e->getMessage());
            return false;
        }
    }

    public function encontrarPorEscola(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pe.*,
                    (SELECT COUNT(*) FROM itens_pedido ip WHERE ip.pedido_id = pe.id) AS total_itens
             FROM pedidos pe
             WHERE pe.usuario_id = ?
             ORDER BY pe.id DESC'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    public function contarPorStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pedidos WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Contagem de pedidos por status respeitando período (dias) e escola.
     */
    public function contarPorStatusPeriodo(int $dias = 0, ?int $escolaId = null): array
    {
        $condicoes = [];
        $params = [];
        if ($dias > 0) {
            $condicoes[] = 'pe.created_at >= ?';
            $params[] = date('Y-m-d', strtotime('-' . ($dias - 1) . ' days')) . ' 00:00:00';
        }
        if ($escolaId) {
            $condicoes[] = 'pe.usuario_id = ?';
            $params[] = (int) $escolaId;
        }
        $where = $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '';

        $stmt = $this->db->prepare(
            "SELECT pe.status, COUNT(*) AS total FROM pedidos pe{$where} GROUP BY pe.status"
        );
        $stmt->execute($params);

        $res = ['realizado' => 0, 'em_andamento' => 0, 'concluido' => 0, 'cancelado' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $res[$row['status']] = (int) $row['total'];
        }
        return $res;
    }

    /**
     * Série diária de pedidos (janela de $dias; 0 = todo o histórico).
     * Retorna ['labels' => [], 'data' => []].
     */
    public function seriePorDia(int $dias = 0, ?string $status = null, ?int $escolaId = null): array
    {
        $condicoes = [];
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
        $where = $condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '';

        $stmt = $this->db->prepare(
            "SELECT DATE(pe.created_at) AS dia, COUNT(*) AS total
             FROM pedidos pe{$where}
             GROUP BY DATE(pe.created_at)
             ORDER BY dia ASC"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($dias > 0) {
            $inicio = date('Y-m-d', strtotime('-' . ($dias - 1) . ' days'));
            $fim = date('Y-m-d');
        } elseif ($rows) {
            $inicio = $rows[0]['dia'];
            $fim = date('Y-m-d');
        } else {
            return ['labels' => [], 'data' => []];
        }

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['dia']] = (int) $row['total'];
        }

        $labels = [];
        $data = [];
        $atual = $inicio;
        while ($atual <= $fim) {
            $labels[] = date('d/m', strtotime($atual));
            $data[] = $counts[$atual] ?? 0;
            $atual = date('Y-m-d', strtotime($atual . ' +1 day'));
        }
        return ['labels' => $labels, 'data' => $data];
    }

    public function escolasQueMaisPedem(int $limite = 10, int $dias = 0, ?string $status = null): array
    {
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
        $where = implode(' AND ', $condicoes);

        $stmt = $this->db->prepare(
            'SELECT u.nome_escola, COUNT(pe.id) AS total_pedidos
             FROM pedidos pe
             INNER JOIN usuarios u ON u.id = pe.usuario_id
             WHERE ' . $where . '
             GROUP BY u.id
             ORDER BY total_pedidos DESC
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