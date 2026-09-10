<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\Produto;

class PedidoService
{
    /**
     * Valida o estoque de todos os itens do carrinho.
     *
     * @return array<int, string> Lista de mensagens de erro (vazia = ok)
     */
    public function validarEstoque(array $itens): array
    {
        if (!$itens) {
            return [];
        }

        $erros = [];
        $produtoModel = new Produto();

        $ids = array_map(fn ($item) => (int) $item['produto']['id'], $itens);
        $atualPorId = [];
        foreach ($produtoModel->findMany($ids) as $atual) {
            $atualPorId[(int) $atual['id']] = $atual;
        }

        foreach ($itens as $item) {
            $produto = $item['produto'];
            $quantidade = (int) $item['quantidade'];
            $id = (int) $produto['id'];

            $atual = $atualPorId[$id] ?? null;
            if (!$atual) {
                $erros[] = "{$produto['nome']} não está mais disponível.";
                continue;
            }

            if ((int) $atual['quantidade_estoque'] <= 0) {
                $erros[] = "{$atual['nome']} está indisponível.";
                continue;
            }

            if ($quantidade > (int) $atual['quantidade_estoque']) {
                $erros[] = "Estoque insuficiente para {$atual['nome']} (disponível: {$atual['quantidade_estoque']}).";
            }
        }

        return $erros;
    }

    /**
     * Cria o pedido e debita o estoque em transação.
     *
     * @return array{pedido_id: int, numero: int}|null null em caso de falha
     */
    public function confirmar(int $usuarioId, array $itens): ?array
    {
        $db = Database::getInstance();
        $pedidoModel = new Pedido();
        $produtoModel = new Produto();
        $itemModel = new ItemPedido();

        $db->beginTransaction();

        try {
            $numero = $pedidoModel->ultimoNumero() + 1;
            $pedidoId = $pedidoModel->create([
                'numero'     => $numero,
                'usuario_id' => $usuarioId,
                'status'     => 'realizado',
            ]);

            foreach ($itens as $item) {
                $produto = $item['produto'];
                $quantidade = (int) $item['quantidade'];
                $id = (int) $produto['id'];

                // Débito atômico: falha se não houver estoque suficiente
                if (!$produtoModel->debitarEstoque($id, $quantidade)) {
                    throw new \RuntimeException("Estoque insuficiente para {$produto['nome']}");
                }

                $itemModel->create($pedidoId, $id, $produto['nome'], $quantidade);
            }

            $db->commit();
            return ['pedido_id' => $pedidoId, 'numero' => $numero];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[PedidoService] Falha ao confirmar pedido: ' . $e->getMessage());
            return null;
        }
    }
}