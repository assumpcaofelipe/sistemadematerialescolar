<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Produto;
use App\Services\CarrinhoService;

class CarrinhoController extends Controller
{
    public function __construct()
    {
        Auth::requireEscola();
    }

    public function index(): void
    {
        $this->viewEscola('escola/carrinho', [
            'itens' => CarrinhoService::itensDetalhados(),
        ]);
    }

    public function adicionar(): void
    {
        if (!Csrf::validate()) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Sessão expirada.'], 419);
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $quantidade = (int) ($_POST['quantidade'] ?? 0);

        if (!$produtoId || $quantidade <= 0) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Dados inválidos.'], 422);
        }

        $produto = (new Produto())->find($produtoId);
        if (!$produto || (int) $produto['status'] !== 1) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Produto indisponível.'], 404);
        }

        CarrinhoService::adicionar($produtoId, $quantidade);

        $this->jsonResponse([
            'ok'      => true,
            'mensagem' => 'Produto adicionado, carrinho atualizado.',
            'total'    => CarrinhoService::totalItens(),
        ]);
    }

    public function atualizar(): void
    {
        if (!Csrf::validate()) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Sessão expirada.'], 419);
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $quantidade = (int) ($_POST['quantidade'] ?? 0);

        if (!$produtoId || $quantidade < 0) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Dados inválidos.'], 422);
        }

        CarrinhoService::atualizar($produtoId, $quantidade);

        $this->jsonResponse([
            'ok'      => true,
            'mensagem' => 'Carrinho atualizado.',
            'total'    => CarrinhoService::totalItens(),
        ]);
    }

    public function remover(): void
    {
        if (!Csrf::validate()) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Sessão expirada.'], 419);
        }

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        CarrinhoService::remover($produtoId);

        $this->jsonResponse([
            'ok'      => true,
            'mensagem' => 'Produto removido.',
            'total'    => CarrinhoService::totalItens(),
        ]);
    }
}