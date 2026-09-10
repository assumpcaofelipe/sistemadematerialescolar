<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Produto;

class CatalogoController extends Controller
{
    private const POR_PAGINA = 20;

    public function __construct()
    {
        Auth::requireEscola();
    }

    public function index(): void
    {
        $busca = trim((string) ($_GET['busca'] ?? ''));
        $categoriaId = (int) ($_GET['categoria'] ?? 0) ?: null;
        $pagina = pagina_atual();

        $produto = new Produto();
        $resultado = $produto->paginar($busca, $categoriaId, $pagina, self::POR_PAGINA, true);

        $queryAntiga = array_filter([
            'busca'     => $busca,
            'categoria' => $categoriaId,
        ], fn ($v) => $v !== '' && $v !== null);

        $this->viewEscola('escola/catalogo', [
            'produtos'    => $resultado['items'],
            'total'       => $resultado['total'],
            'pagina'      => $pagina,
            'categorias'  => (new Categoria())->all(true),
            'busca'       => $busca,
            'categoriaId' => $categoriaId,
            'paginacao'   => paginacao_html($resultado['total'], self::POR_PAGINA, $pagina, '/catalogo', $queryAntiga),
        ]);
    }
}