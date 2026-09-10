<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check() && Auth::isEscola()) {
            $this->redirect('/catalogo');
        }
        $this->view('auth/login', ['erro' => $this->getFlash('auth_error')]);
    }

    public function login(): void
    {
        if (!Csrf::validate()) {
            $this->flash('auth_error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/');
        }

        $email = trim($_POST['email'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');

        [$ok, $usuario] = Auth::attempt($email, $senha);

        if (!$ok) {
            $this->flash('auth_error', 'Usuário ou senha inválidos.');
            $this->redirect('/');
        }

        if ($usuario['tipo'] !== 'escola') {
            Auth::logout();
            $this->flash('auth_error', 'Acesso restrito. Use o painel administrativo.');
            $this->redirect('/');
        }

        if ((int) $usuario['status'] !== 1) {
            Auth::logout();
            $this->flash('auth_error', 'A conta está inativa. Procure a secretaria.');
            $this->redirect('/');
        }

        $this->redirect('/catalogo');
    }

    public function recuperarSenha(): void
    {
        if (!Csrf::validate()) {
            $this->jsonResponse(['erro' => 'Sessão expirada. Recarregue a página.'], 400);
        }

        $email  = trim((string) ($_POST['email'] ?? ''));
        $senha  = (string) ($_POST['senha'] ?? '');
        $senha2 = (string) ($_POST['senha_confirmar'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['erro' => 'Informe um e-mail válido.'], 400);
        }
        if (mb_strlen($senha) < 4) {
            $this->jsonResponse(['erro' => 'A nova senha deve ter pelo menos 4 caracteres.'], 400);
        }
        if ($senha !== $senha2) {
            $this->jsonResponse(['erro' => 'As senhas não conferem.'], 400);
        }

        $usuario = Usuario::findByEmail($email);
        if (!$usuario || $usuario['tipo'] !== 'escola') {
            $this->jsonResponse(['erro' => 'Nenhuma escola cadastrada com este e-mail.'], 404);
        }
        if ((int) $usuario['status'] !== 1) {
            $this->jsonResponse(['erro' => 'A conta está inativa. Procure a secretaria.'], 403);
        }

        (new Usuario())->updateSenha((int) $usuario['id'], password_hash($senha, PASSWORD_DEFAULT));
        $this->jsonResponse([
            'mensagem' => 'Senha atualizada com sucesso. Você já pode entrar com a nova senha.',
            'nome'     => $usuario['nome_escola'] ?? $usuario['nome'],
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }
}