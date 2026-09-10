<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Pedido;
use App\Services\CarrinhoService;
use App\Services\NotificacaoService;
use App\Services\PedidoService;

class PedidoController extends Controller
{
    private const POR_PAGINA = 10;

    public function __construct()
    {
        Auth::requireEscola();
    }

    public function revisao(): void
    {
        if (CarrinhoService::vazio()) {
            $this->redirect('/catalogo');
        }

        $this->viewEscola('escola/revisao', [
            'itens' => CarrinhoService::itensDetalhados(),
        ]);
    }

    public function confirmar(): void
    {
        if (!Csrf::validate()) {
            $this->flash('pedido_erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/pedido/revisao');
        }

        if (CarrinhoService::vazio()) {
            $this->redirect('/catalogo');
        }

        $itens = CarrinhoService::itensDetalhados();

        // 1. Validar estoque novamente
        $pedidoService = new PedidoService();
        $erros = $pedidoService->validarEstoque($itens);

        if ($erros) {
            $this->flash('pedido_erro', 'Um ou mais produtos do seu pedido ficaram indisponíveis ou com estoque insuficiente. Ajuste as quantidades para continuar.');
            $this->flash('pedido_detalhes', $erros);
            $this->redirect('/pedido/revisao');
        }

        $usuario = Auth::user();

        // 2-4. Salvar pedido, debitar estoque e gerar número (transação)
        $resultado = $pedidoService->confirmar((int) $usuario['id'], $itens);

        if (!$resultado) {
            $this->flash('pedido_erro', 'Não foi possível concluir o pedido. Adicione novamente os itens.');
            $this->redirect('/pedido/revisao');
        }

        CarrinhoService::limpar();

        // 5. Notificações (e-mail primeiro, depois WhatsApp) — nunca quebram o fluxo
        $notificacao = new NotificacaoService();

        $itensPedido = array_map(function ($item) {
            return [
                'produto_id'    => (int) $item['produto']['id'],
                'produto_nome'  => $item['produto']['nome'],
                'quantidade'    => (int) $item['quantidade'],
            ];
        }, $itens);

        $notificacao->emailNovoPedido(
            (int) $resultado['numero'],
            (string) ($usuario['nome_escola'] ?? $usuario['nome']),
            $itensPedido
        );

        // 6. Monta mensagem pronta do WhatsApp
        $mensagem = $notificacao->montarMensagemWhatsApp(
            (string) ($usuario['nome_escola'] ?? $usuario['nome']),
            (int) $resultado['numero'],
            $itensPedido
        );
        $linkWhatsApp = $notificacao->linkWhatsApp($mensagem);

        \App\Core\Session::set('pedido_sucesso', [
            'numero'      => (int) $resultado['numero'],
            'link_whats'  => $linkWhatsApp,
            'mensagem'    => $mensagem,
        ]);

        // 7. Tela de sucesso
        $this->redirect('/pedido/sucesso');
    }

    public function sucesso(): void
    {
        $dados = \App\Core\Session::get('pedido_sucesso');

        if (!$dados) {
            $this->redirect('/catalogo');
        }

        \App\Core\Session::remove('pedido_sucesso');

        $this->viewEscola('escola/sucesso', [
            'numero'     => (int) $dados['numero'],
            'linkWhats'  => $dados['link_whats'],
            'mensagem'   => $dados['mensagem'],
        ]);
    }

    public function historico(): void
    {
        $usuario = Auth::user();
        $status  = trim((string) ($_GET['status'] ?? ''));
        $pagina  = pagina_atual();

        $pedido = new Pedido();
        $resultado = $pedido->paginar(
            ['status' => $status, 'escola_id' => (int) $usuario['id']],
            $pagina,
            self::POR_PAGINA
        );

        $queryAntiga = [];
        if ($status !== '') {
            $queryAntiga['status'] = $status;
        }

        $this->viewEscola('escola/historico', [
            'pedidos'   => $resultado['items'],
            'total'     => $resultado['total'],
            'pagina'    => $pagina,
            'status'    => $status,
            'paginacao' => paginacao_html($resultado['total'], self::POR_PAGINA, $pagina, '/pedidos', $queryAntiga),
        ]);
    }

    public function detalhe(int $numero): void
    {
        $pedido = (new Pedido())->findByNumero($numero);

        if (!$pedido || (int) $pedido['usuario_id'] !== (int) (Auth::user()['id'] ?? 0)) {
            $this->flash('pedido_erro', 'Pedido não encontrado.');
            $this->redirect('/pedidos');
        }

        $this->viewEscola('escola/detalhe', [
            'pedido' => $pedido,
            'itens'  => (new Pedido())->itens((int) $pedido['id']),
        ]);
    }
}