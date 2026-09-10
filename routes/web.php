<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CarrinhoController;
use App\Controllers\CatalogoController;
use App\Controllers\PedidoController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\CategoriaController;
use App\Controllers\Admin\ConfiguracaoController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PedidoController as AdminPedidoController;
use App\Controllers\Admin\ProdutoController;
use App\Controllers\Admin\UsuarioController;

return [
    'GET' => [
        '/'                    => [AuthController::class, 'showLogin'],
        '/logout'              => [AuthController::class, 'logout'],

        '/catalogo'            => [CatalogoController::class, 'index'],
        '/carrinho'            => [CarrinhoController::class, 'index'],
        '/pedidos'             => [PedidoController::class, 'historico'],
        '/pedidos/{numero}'    => [PedidoController::class, 'detalhe'],
        '/pedido/revisao'      => [PedidoController::class, 'revisao'],
        '/pedido/sucesso'      => [PedidoController::class, 'sucesso'],

        '/admin/login'         => [AdminAuthController::class, 'showLogin'],
        '/admin/logout'        => [AdminAuthController::class, 'logout'],
        '/admin'               => [DashboardController::class, 'index'],
        '/admin/dashboard'     => [DashboardController::class, 'index'],

        '/admin/pedidos'       => [AdminPedidoController::class, 'index'],
        '/admin/pedidos/{numero}' => [AdminPedidoController::class, 'show'],

        '/admin/categorias'    => [CategoriaController::class, 'index'],
        '/admin/categorias/nova' => [CategoriaController::class, 'create'],
        '/admin/categorias/editar/{id}' => [CategoriaController::class, 'edit'],

        '/admin/produtos'      => [ProdutoController::class, 'index'],
        '/admin/produtos/novo' => [ProdutoController::class, 'create'],
        '/admin/produtos/editar/{id}' => [ProdutoController::class, 'edit'],
        '/admin/estoque'       => [ProdutoController::class, 'estoque'],

        '/admin/usuarios'      => [UsuarioController::class, 'index'],
        '/admin/usuarios/novo' => [UsuarioController::class, 'create'],
        '/admin/usuarios/editar/{id}' => [UsuarioController::class, 'edit'],
        '/admin/usuarios/admin/novo' => [UsuarioController::class, 'createAdmin'],

        '/admin/configuracoes' => [ConfiguracaoController::class, 'index'],
    ],
    'POST' => [
        '/login'               => [AuthController::class, 'login'],
        '/recuperar-senha'     => [AuthController::class, 'recuperarSenha'],
        '/admin/login'         => [AdminAuthController::class, 'login'],
        '/admin/recuperar-senha' => [AdminAuthController::class, 'recuperarSenha'],

        '/carrinho/adicionar'  => [CarrinhoController::class, 'adicionar'],
        '/carrinho/atualizar'  => [CarrinhoController::class, 'atualizar'],
        '/carrinho/remover'    => [CarrinhoController::class, 'remover'],

        '/pedido/confirmar'    => [PedidoController::class, 'confirmar'],

        '/admin/pedidos/status/{id}' => [AdminPedidoController::class, 'updateStatus'],
        '/admin/pedidos/excluir/{id}' => [AdminPedidoController::class, 'destroy'],

        '/admin/categorias/salvar' => [CategoriaController::class, 'store'],
        '/admin/categorias/atualizar/{id}' => [CategoriaController::class, 'update'],
        '/admin/categorias/excluir/{id}' => [CategoriaController::class, 'destroy'],

        '/admin/produtos/salvar' => [ProdutoController::class, 'store'],
        '/admin/produtos/atualizar/{id}' => [ProdutoController::class, 'update'],
        '/admin/produtos/excluir/{id}' => [ProdutoController::class, 'destroy'],
        '/admin/produtos/estoque/{id}' => [ProdutoController::class, 'ajustarEstoque'],

        '/admin/usuarios/salvar' => [UsuarioController::class, 'store'],
        '/admin/usuarios/atualizar/{id}' => [UsuarioController::class, 'update'],
        '/admin/usuarios/excluir/{id}' => [UsuarioController::class, 'destroy'],
        '/admin/usuarios/admin/salvar' => [UsuarioController::class, 'storeAdmin'],

        '/admin/configuracoes' => [ConfiguracaoController::class, 'update'],
    ],
];