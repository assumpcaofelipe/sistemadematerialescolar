<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Usuario;

class DashboardController extends Controller
{
    public function __construct()
    {
        Auth::requireAcessoAdmin();
    }

    public function index(): void
    {
        $pedido = new Pedido();
        $produto = new Produto();

        $periodoDias = (int) ($_GET['periodo'] ?? 15);
        if (!in_array($periodoDias, [7, 15, 30, 90], true)) {
            $periodoDias = 0; // "todos" no filtro = 0
        }
        $status      = trim((string) ($_GET['status'] ?? ''));
        $escolaId    = (int) ($_GET['escola'] ?? 0);
        $categoriaId = (int) ($_GET['categoria'] ?? 0);

        $filtros = ['periodo' => $periodoDias, 'status' => $status, 'escola' => $escolaId, 'categoria' => $categoriaId];

        $statusPeriodo = $pedido->contarPorStatusPeriodo(
            $periodoDias > 0 ? $periodoDias : 0,
            $escolaId ?: null
        );

        $serie = $pedido->seriePorDia($periodoDias > 0 ? $periodoDias : 0, $status ?: null, $escolaId ?: null);

        $topProdutos = $produto->maisPedidos(8, $periodoDias, $status ?: null, $escolaId ?: null, $categoriaId ?: null);
        $topEscolas  = $pedido->escolasQueMaisPedem(8, $periodoDias, $status ?: null);

        $this->view('admin/dashboard', [
            'statusPeriodo'   => $statusPeriodo,
            'serie'           => $serie,
            'topProdutos'     => $topProdutos,
            'topEscolas'      => $topEscolas,
            'escolas'         => (new Usuario())->all('escola'),
            'categorias'      => (new Categoria())->all(),
            'filtros'         => $filtros,
            'filtrosAtivos'   => $periodoDias || $status !== '' || $escolaId || $categoriaId,
        ]);
    }
}