<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Pedido;
use App\Models\ItemPedido;

class PedidoService
{
    /**
     * Cria o pedido sem movimentar o estoque.
     *
     * @return array{pedido_id: int, numero: int}|null null em caso de falha
     */
    public function confirmar(int $usuarioId, array $itens): ?array
    {
        $db = Database::getInstance();
        $pedidoModel = new Pedido();
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