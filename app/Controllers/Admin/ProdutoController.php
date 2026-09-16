<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Categoria;
use App\Models\Produto;

class ProdutoController extends Controller
{
    private const POR_PAGINA = 20;
    private const MAX_TAMANHO_IMAGEM = 2 * 1024 * 1024;
    private const EXTENSOES_IMAGEM = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const LIMITE_BAIXO_ESTOQUE = 5;

    public function __construct()
    {
        Auth::requireAcessoAdmin();
    }

    public function index(): void
    {
        $busca = trim((string) ($_GET['busca'] ?? ''));
        $categoriaId = (int) ($_GET['categoria'] ?? 0) ?: null;
        $pagina = pagina_atual();

        $produto = new Produto();
        $resultado = $produto->paginar($busca, $categoriaId, $pagina, self::POR_PAGINA);

        $categorias = (new Categoria())->all();

        $queryAntiga = array_filter([
            'busca'     => $busca,
            'categoria' => $categoriaId,
        ], fn ($v) => $v !== '' && $v !== null);

        $this->view('admin/produtos/index', [
            'produtos'     => $resultado['items'],
            'total'        => $resultado['total'],
            'porPagina'    => self::POR_PAGINA,
            'pagina'       => $pagina,
            'categorias'   => $categorias,
            'busca'        => $busca,
            'categoriaId'  => $categoriaId,
            'paginacao'    => paginacao_html($resultado['total'], self::POR_PAGINA, $pagina, '/admin/produtos', $queryAntiga),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/produtos/form', [
            'produto'     => null,
            'categorias'  => (new Categoria())->all(),
        ]);
    }

    public function estoque(): void
    {
        $busca = trim((string) ($_GET['busca'] ?? ''));
        $categoriaId = (int) ($_GET['categoria'] ?? 0) ?: null;
        $situacao = trim((string) ($_GET['situacao'] ?? ''));
        if (!in_array($situacao, ['', 'baixo', 'zerado'], true)) {
            $situacao = '';
        }
        $pagina = pagina_atual();

        $resultado = (new Produto())->paginarEstoque(
            $busca,
            $categoriaId,
            $situacao,
            self::LIMITE_BAIXO_ESTOQUE,
            $pagina,
            self::POR_PAGINA
        );

        $queryAntiga = array_filter([
            'busca'     => $busca,
            'categoria' => $categoriaId,
            'situacao'  => $situacao,
        ], fn ($v) => $v !== '' && $v !== null);

        $this->view('admin/produtos/estoque', [
            'produtos'    => $resultado['items'],
            'total'       => $resultado['total'],
            'pagina'      => $pagina,
            'busca'       => $busca,
            'categoriaId' => $categoriaId,
            'situacao'    => $situacao,
            'categorias'  => (new Categoria())->all(),
            'limiteBaixo' => self::LIMITE_BAIXO_ESTOQUE,
            'paginacao'   => paginacao_html($resultado['total'], self::POR_PAGINA, $pagina, '/admin/estoque', $queryAntiga),
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/produtos');
        }

        $dados = $this->dadosDoFormulario();
        $dados['imagem'] = $this->processarUpload();
        $this->salvar($dados);
    }

    public function edit(int $id): void
    {
        $produto = (new Produto())->find($id);

        if (!$produto) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/admin/produtos');
        }

        $this->view('admin/produtos/form', [
            'produto'    => $produto,
            'categorias' => (new Categoria())->all(),
        ]);
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/produtos');
        }

        $dados = $this->dadosDoFormulario();
        $dados['id'] = $id;

        $model = new Produto();
        $atual = $model->find($id);
        if (!$atual) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect('/admin/produtos');
        }

        // Nova imagem enviada = substitui a atual.
        // Senão, se marcou "remover", apaga a atual; caso contrário mantém.
        $dados['imagem'] = $this->resolverImagem(
            $atual['imagem'] ?? null,
            !empty($_POST['remover_imagem'])
        );

        $this->salvar($dados);
    }

    public function destroy(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/produtos');
        }

        $model = new Produto();
        $produto = $model->find($id);
        if ($produto) {
            $this->apagarArquivo($produto['imagem'] ?? null);
            $model->delete($id);
            $this->flash('success', 'Produto excluído.');
        }

        $this->redirect('/admin/produtos');
    }

    public function ajustarEstoque(int $id): void
    {
        if (!Csrf::validate()) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Sessão expirada.'], 419);
        }

        $quantidade = (int) ($_POST['quantidade'] ?? -1);
        if ($quantidade < 0) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Quantidade inválida.'], 422);
        }

        $model = new Produto();
        if (!$model->find($id)) {
            $this->jsonResponse(['ok' => false, 'mensagem' => 'Produto não encontrado.'], 404);
        }

        $model->ajustarEstoque($id, $quantidade);
        $this->jsonResponse(['ok' => true, 'quantidade' => $quantidade]);
    }

    private function dadosDoFormulario(): array
    {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        return [
            'categoria_id'       => (int) ($_POST['categoria_id'] ?? 0),
            'nome'               => $nome,
            'slug'               => trim((string) ($_POST['slug'] ?? '')) ?: slugify($nome),
            'descricao'          => trim((string) ($_POST['descricao'] ?? '')),
            'quantidade_estoque' => (int) ($_POST['quantidade_estoque'] ?? 0),
            'status'             => isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0,
        ];
    }

    private function processarUpload(): ?string
    {
        if (empty($_FILES['imagem']) || $_FILES['imagem']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Falha no upload da imagem.');
            return null;
        }

        if ($_FILES['imagem']['size'] > self::MAX_TAMANHO_IMAGEM) {
            $this->flash('error', 'A imagem deve ter no máximo 2MB.');
            return null;
        }

        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, self::EXTENSOES_IMAGEM, true)) {
            $this->flash('error', 'Formato de imagem inválido.');
            return null;
        }

        $mime = mime_content_type($_FILES['imagem']['tmp_name']);
        if (!str_starts_with($mime, 'image/')) {
            $this->flash('error', 'O arquivo enviado não é uma imagem.');
            return null;
        }

        $nomeArquivo = bin2hex(random_bytes(8)) . '.' . $extensao;
        $destino = __DIR__ . '/../../../public/uploads/' . $nomeArquivo;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $this->flash('error', 'Não foi possível salvar a imagem.');
            return null;
        }

        return 'uploads/' . $nomeArquivo;
    }

    /**
     * Decide qual imagem fica no produto: a nova enviada, mantém a atual
     * ou remove (ficando sem foto). Apaga o arquivo antigo quando troca.
     */
    private function resolverImagem(?string $imagemAtual, bool $remover): ?string
    {
        $nova = $this->processarUpload();

        if ($nova !== null) {
            $this->apagarArquivo($imagemAtual);
            return $nova;
        }

        if ($remover) {
            $this->apagarArquivo($imagemAtual);
            return null;
        }

        return $imagemAtual;
    }

    private function apagarArquivo(?string $imagem): void
    {
        if (!$imagem) {
            return;
        }
        $caminho = __DIR__ . '/../../../public/' . $imagem;
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }

    private function salvar(array $dados): void
    {
        if ($dados['nome'] === '' || $dados['descricao'] === '' || !$dados['categoria_id']) {
            $this->flash('error', 'Preencha nome, descrição e categoria.');
            $this->redirect(isset($dados['id'])
                ? '/admin/produtos/editar/' . $dados['id']
                : '/admin/produtos/novo');
        }

        $model = new Produto();
        $existente = $model->findBySlug($dados['slug']);

        if (!isset($dados['id'])) {
            if ($existente && strcasecmp($existente['slug'], $dados['slug']) === 0) {
                $this->flash('error', 'Já existe um produto com este slug.');
                $this->redirect('/admin/produtos/novo');
            }
            $model->create($dados);
            $this->flash('success', 'Produto criado.');
        } else {
            if ($existente && (int) $existente['id'] !== (int) $dados['id']) {
                $this->flash('error', 'Já existe um produto com este slug.');
                $this->redirect('/admin/produtos/editar/' . $dados['id']);
            }
            $model->update((int) $dados['id'], $dados);
            $this->flash('success', 'Produto atualizado.');
        }

        $this->redirect('/admin/produtos');
    }
}