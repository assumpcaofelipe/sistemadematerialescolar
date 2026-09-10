<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

class Auth
{
    public const TIPO_ESCOLA = 'escola';
    public const TIPO_ADMIN = 'admin';
    public const TIPO_SUPERVISOR = 'supervisor';

    private static ?array $usuarioCache = null;
    private static ?int $usuarioCacheId = null;

    public static function attempt(string $email, string $senha): array
    {
        $usuario = Usuario::findByEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            self::login($usuario);
            return [true, $usuario];
        }

        return [false, null];
    }

    public static function login(array $usuario): void
    {
        Session::regenerateId();
        Session::set('usuario_id', (int) $usuario['id']);
        Session::set('usuario_tipo', $usuario['tipo']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('usuario_id');
    }

    public static function user(): ?array
    {
        $id = Session::get('usuario_id');
        if (!$id) {
            return null;
        }

        if (self::$usuarioCacheId === (int) $id && self::$usuarioCache !== null) {
            return self::$usuarioCache;
        }

        self::$usuarioCache = Usuario::find((int) $id);
        self::$usuarioCacheId = (int) $id;
        return self::$usuarioCache;
    }

    public static function id(): ?int
    {
        return Session::get('usuario_id');
    }

    public static function tipo(): ?string
    {
        return Session::get('usuario_tipo');
    }

    public static function isEscola(): bool
    {
        return self::tipo() === 'escola';
    }

    public static function isAdmin(): bool
    {
        return self::tipo() === self::TIPO_ADMIN;
    }

    public static function isSupervisor(): bool
    {
        return self::tipo() === self::TIPO_SUPERVISOR;
    }

    /**
     * Usuário do painel administrativo (admin ou supervisor).
     */
    public static function isAdminArea(): bool
    {
        $tipo = self::tipo();
        return $tipo === self::TIPO_ADMIN || $tipo === self::TIPO_SUPERVISOR;
    }

    public static function requireEscola(): void
    {
        if (!self::check() || !self::isEscola()) {
            self::redirectToLogin();
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::check() || !self::isAdmin()) {
            self::redirectToLogin();
        }
    }

    /**
     * Acesso ao painel administrativo (admin ou supervisor).
     * Chamado por todos os módulos, exceto o de usuários (só admin).
     */
    public static function requireAcessoAdmin(): void
    {
        if (!self::check() || !self::isAdminArea()) {
            self::redirectToLogin();
        }
    }

    private static function redirectToLogin(): void
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (str_starts_with($uri, '/admin')) {
            header('Location: /admin/login');
        } else {
            header('Location: /');
        }
        exit;
    }
}