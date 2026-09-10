<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\StatusPedido;
use App\Models\Pedido;
use App\Models\Usuario;

class PedidoController extends Controller
{
    private const POR_PAGINA = 20;

    public function __construct()
    {
        Auth::requireAcessoAdmin();
    }

    public function index(): void
    {
        $status   = trim((string) ($_GET['status'] ?? ''));
        $escolaId = (int) ($_GET['escola'] ?? 0);
        $pagina   = pagina_atual();

        $pedido = new Pedido();
        $resultado = $pedido->paginar(
            ['status' => $status, 'escola_id' => $escolaId ?: null],
            $pagina,
            self::POR_PAGINA
        );

        $queryAntiga = [];
        if ($status !== '') {
            $queryAntiga['status'] = $status;
        }
        if ($escolaId) {
            $queryAntiga['escola'] = $escolaId;
        }

        $this->view('admin/pedidos/index', [
            'pedidos'   => $resultado['items'],
            'total'     => $resultado['total'],
            'pagina'    => $pagina,
            'status'    => $status,
            'escolaId'  => $escolaId,
            'escolas'   => (new Usuario())->all('escola'),
            'paginacao' => paginacao_html($resultado['total'], self::POR_PAGINA, $pagina, '/admin/pedidos', $queryAntiga),
        ]);
    }

    public function show(int $numero): void
    {
        $pedido = (new Pedido())->findByNumero($numero);

        if (!$pedido) {
            $this->flash('error', 'Pedido não encontrado.');
            $this->redirect('/admin/pedidos');
        }

        $model = new Pedido();
        $this->view('admin/pedidos/show', [
            'pedido' => $pedido,
            'itens'  => $model->itens((int) $pedido['id']),
        ]);
    }

    public function destroy(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/pedidos');
        }

        $model = new Pedido();
        $pedido = $model->find($id);

        if (!$pedido) {
            $this->flash('error', 'Pedido não encontrado.');
            $this->redirect('/admin/pedidos');
        }

        if ($model->excluirComRestauro((int) $id)) {
            $this->flash('success', 'Pedido #' . (int) $pedido['numero'] . ' excluído e estoque restaurado.');
        } else {
            $this->flash('error', 'Não foi possível excluir o pedido.');
        }
        $this->redirect('/admin/pedidos');
    }

    public function updateStatus(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/pedidos');
        }

        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, StatusPedido::todos(), true)) {
            $this->flash('error', 'Status inválido.');
            $this->redirect('/admin/pedidos');
        }

        $model = new Pedido();
        $pedido = $model->find($id);

        if (!$pedido) {
            $this->flash('error', 'Pedido não encontrado.');
            $this->redirect('/admin/pedidos');
        }

        $model->atualizarStatus($id, $status);
        $this->flash('success', 'Status do pedido atualizado.');
        $this->redirect('/admin/pedidos/' . (int) $pedido['numero']);
    }
}