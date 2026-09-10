<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function __construct()
    {
        Auth::requireAcessoAdmin();
    }

    public function index(): void
    {
        $model = new Categoria();
        $this->view('admin/categorias/index', [
            'categorias' => $model->all(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/categorias/form', ['categoria' => null]);
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/categorias');
        }

        $dados = $this->dadosDoFormulario();
        $this->salvar($dados);
    }

    public function edit(int $id): void
    {
        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            $this->flash('error', 'Categoria não encontrada.');
            $this->redirect('/admin/categorias');
        }

        $this->view('admin/categorias/form', ['categoria' => $categoria]);
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/categorias');
        }

        $dados = $this->dadosDoFormulario();
        $dados['id'] = $id;
        $this->salvar($dados);
    }

    public function destroy(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/categorias');
        }

        $model = new Categoria();
        $categoria = $model->find($id);

        if (!$categoria) {
            $this->flash('error', 'Categoria não encontrada.');
            $this->redirect('/admin/categorias');
        }

        $produtos = $model->countProdutos($id);
        if ($produtos > 0) {
            $this->flash('error', 'Não é possível excluir: existem produtos vinculados a esta categoria.');
            $this->redirect('/admin/categorias');
        }

        $model->delete($id);
        $this->flash('success', 'Categoria excluída.');
        $this->redirect('/admin/categorias');
    }

    private function dadosDoFormulario(): array
    {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        return [
            'nome'   => $nome,
            'slug'   => trim((string) ($_POST['slug'] ?? '')) ?: slugify($nome),
            'status' => isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0,
            'ordem'  => (int) ($_POST['ordem'] ?? 0),
        ];
    }

    private function salvar(array $dados): void
    {
        if ($dados['nome'] === '') {
            $this->flash('error', 'Informe o nome da categoria.');
            $this->redirect('/admin/categorias');
        }

        $model = new Categoria();
        $existente = $model->findBySlug($dados['slug']);

        if (!isset($dados['id'])) {
            if ($existente) {
                $this->flash('error', 'Já existe uma categoria com este slug.');
                $this->redirect('/admin/categorias/nova');
            }
            $model->create($dados);
            $this->flash('success', 'Categoria criada.');
        } else {
            if ($existente && (int) $existente['id'] !== (int) $dados['id']) {
                $this->flash('error', 'Já existe uma categoria com este slug.');
                $this->redirect('/admin/categorias/editar/' . $dados['id']);
            }
            $model->update((int) $dados['id'], $dados);
            $this->flash('success', 'Categoria atualizada.');
        }

        $this->redirect('/admin/categorias');
    }
}