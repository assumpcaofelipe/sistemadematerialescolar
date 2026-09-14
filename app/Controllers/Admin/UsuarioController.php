<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function index(): void
    {
        $this->view('admin/usuarios/index', [
            'usuarios' => (new Usuario())->all('escola'),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/usuarios/form', [
            'usuario'  => null,
            'senhaGerada' => $this->gerarSenha(),
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/usuarios');
        }

        $dados = $this->dadosDoFormulario();
        $this->salvar($dados);
    }

    public function createAdmin(): void
    {
        $this->view('admin/usuarios/form_admin', [
            'senhaGerada' => $this->gerarSenha(),
            'usuarios'    => (new Usuario())->allSistema(),
            'url'         => '/admin/usuarios/admin/salvar',
        ]);
    }

    public function storeAdmin(): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $dados = $this->dadosAdminDoFormulario();
        $this->salvarAdmin($dados);
    }

    public function edit(int $id): void
    {
        $usuario = (new Usuario())->find($id);

        if (!$usuario || $usuario['tipo'] !== 'escola') {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $this->view('admin/usuarios/form', [
            'usuario'   => $usuario,
            'senhaGerada' => $this->gerarSenha(),
        ]);
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/usuarios');
        }

        $dados = $this->dadosDoFormulario();
        $dados['id'] = $id;
        $this->salvar($dados);
    }

    public function destroy(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/usuarios');
        }

        if ($id === Auth::id()) {
            $this->flash('error', 'Você não pode excluir o próprio usuário.');
            $this->redirect('/admin/usuarios');
        }

        try {
            (new Usuario())->delete($id);
            $this->flash('success', 'Escola excluída.');
        } catch (\PDOException $e) {
            $this->flash('error', 'Não foi possível excluir: esta escola possui pedidos vinculados.');
        }
        $this->redirect('/admin/usuarios');
    }

    public function editAdmin(int $id): void
    {
        $usuario = (new Usuario())->find($id);

        if (!$usuario || !in_array($usuario['tipo'], [Auth::TIPO_ADMIN, Auth::TIPO_SUPERVISOR], true)) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $this->view('admin/usuarios/form_admin', [
            'usuario'    => $usuario,
            'senhaGerada' => $this->gerarSenha(),
            'url'        => '/admin/usuarios/admin/atualizar/' . (int) $usuario['id'],
            'usuarios'   => (new Usuario())->allSistema(),
        ]);
    }

    public function updateAdmin(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $dados = $this->dadosAdminDoFormulario();
        $dados['id'] = $id;
        $this->salvarAdmin($dados);
    }

    public function destroyAdmin(int $id): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        if ($id === Auth::id()) {
            $this->flash('error', 'Você não pode excluir o próprio usuário.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $usuario = (new Usuario())->find($id);
        if (!$usuario || !in_array($usuario['tipo'], [Auth::TIPO_ADMIN, Auth::TIPO_SUPERVISOR], true)) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        if ($usuario['tipo'] === Auth::TIPO_ADMIN && (new Usuario())->countByTipo(Auth::TIPO_ADMIN) <= 1) {
            $this->flash('error', 'Não é possível excluir: é necessário manter ao menos um administrador.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        try {
            (new Usuario())->delete($id);
            $this->flash('success', 'Usuário do sistema excluído.');
        } catch (\PDOException $e) {
            $this->flash('error', 'Não foi possível excluir este usuário no momento.');
        }
        $this->redirect('/admin/usuarios/admin/novo');
    }

    private function dadosDoFormulario(): array
    {
        $nomeEscola = trim((string) ($_POST['nome_escola'] ?? ''));
        return [
            'nome'        => $nomeEscola,
            'email'       => trim((string) ($_POST['email'] ?? '')),
            'senha'       => (string) ($_POST['senha'] ?? ''),
            'nome_escola' => $nomeEscola,
            'status'      => 1,
            'tipo'        => 'escola',
        ];
    }

    private function salvar(array $dados): void
    {
        if ($dados['nome'] === '' || $dados['email'] === '') {
            $this->flash('error', 'Preencha nome da escola e e-mail.');
            $this->redirect('/admin/usuarios');
        }

        $model = new Usuario();
        $existente = Usuario::findByEmail($dados['email']);

        if (!isset($dados['id'])) {
            if ($existente) {
                $this->flash('error', 'Já existe um usuário com este e-mail.');
                $this->redirect('/admin/usuarios/novo');
            }
            if (mb_strlen($dados['senha']) < 4) {
                $this->flash('error', 'Informe uma senha com pelo menos 4 caracteres.');
                $this->redirect('/admin/usuarios/novo');
            }
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $model->create($dados);
            $this->flash('success', 'Escola cadastrada.');
        } else {
            if ($existente && (int) $existente['id'] !== (int) $dados['id']) {
                $this->flash('error', 'Já existe um usuário com este e-mail.');
                $this->redirect('/admin/usuarios/editar/' . $dados['id']);
            }
            $atual = $model->find((int) $dados['id']);
            if ($atual) {
                $dados['status'] = (int) $atual['status'];
            }
            if ($dados['senha'] !== '') {
                $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            }
            $model->update((int) $dados['id'], $dados);
            $this->flash('success', 'Escola atualizada.');
        }

        $this->redirect('/admin/usuarios');
    }

    private function dadosAdminDoFormulario(): array
    {
        return [
            'nome'   => trim((string) ($_POST['nome'] ?? '')),
            'email'  => trim((string) ($_POST['email'] ?? '')),
            'senha'  => (string) ($_POST['senha'] ?? ''),
            'status' => isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0,
            'tipo'   => ($_POST['tipo'] ?? '') === Auth::TIPO_SUPERVISOR
                ? Auth::TIPO_SUPERVISOR
                : Auth::TIPO_ADMIN,
        ];
    }

    private function salvarAdmin(array $dados): void
    {
        if ($dados['nome'] === '' || $dados['email'] === '') {
            $this->flash('error', 'Preencha nome e e-mail.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Informe um e-mail válido.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $existente = Usuario::findByEmail($dados['email']);

        if (!isset($dados['id'])) {
            if ($existente) {
                $this->flash('error', 'Já existe um usuário com este e-mail.');
                $this->redirect('/admin/usuarios/admin/novo');
            }

            if (mb_strlen($dados['senha']) < 4) {
                $this->flash('error', 'Informe uma senha com pelo menos 4 caracteres.');
                $this->redirect('/admin/usuarios/admin/novo');
            }

            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $dados['nome_escola'] = null;
            (new Usuario())->create($dados);
            $this->flash('success', 'Usuário do sistema cadastrado.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        $atual = (new Usuario())->find((int) $dados['id']);
        if (!$atual || !in_array($atual['tipo'], [Auth::TIPO_ADMIN, Auth::TIPO_SUPERVISOR], true)) {
            $this->flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios/admin/novo');
        }

        if ($existente && (int) $existente['id'] !== (int) $dados['id']) {
            $this->flash('error', 'Já existe um usuário com este e-mail.');
            $this->redirect('/admin/usuarios/admin/editar/' . (int) $dados['id']);
        }

        // Impede rebaixar/deixar sem admin ativo o último administrador existente.
        $seraAtivo = ($dados['status'] === 1);
        $demovendoUltimoAdmin = $atual['tipo'] === Auth::TIPO_ADMIN
            && $dados['tipo'] !== Auth::TIPO_ADMIN
            && (new Usuario())->countByTipo(Auth::TIPO_ADMIN) <= 1;
        $desativandoUltimoAdmin = $atual['tipo'] === Auth::TIPO_ADMIN
            && $dados['tipo'] === Auth::TIPO_ADMIN
            && !$seraAtivo
            && (new Usuario())->countByTipo(Auth::TIPO_ADMIN) <= 1;

        if ($demovendoUltimoAdmin || $desativandoUltimoAdmin) {
            $this->flash('error', 'Não é possível alterar: é necessário manter ao menos um administrador ativo.');
            $this->redirect('/admin/usuarios/admin/editar/' . (int) $dados['id']);
        }

        if ($dados['senha'] !== '') {
            if (mb_strlen($dados['senha']) < 4) {
                $this->flash('error', 'Informe uma senha com pelo menos 4 caracteres.');
                $this->redirect('/admin/usuarios/admin/editar/' . (int) $dados['id']);
            }
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        $dados['nome_escola'] = null;
        (new Usuario())->update((int) $dados['id'], $dados);

        // Se alterou a própria função, encerra a sessão para aplicar o novo perfil.
        if ((int) $dados['id'] === Auth::id() && $dados['tipo'] !== $atual['tipo']) {
            Auth::logout();
            $this->flash('success', 'Perfil atualizado. Entre com a nova função.');
            $this->redirect('/admin/login');
            return;
        }

        $this->flash('success', 'Usuário do sistema atualizado.');
        $this->redirect('/admin/usuarios/admin/novo');
    }

    private function gerarSenha(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }
}