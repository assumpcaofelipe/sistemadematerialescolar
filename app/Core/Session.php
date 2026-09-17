<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public const AREA_ADMIN = 'admin';
    public const AREA_ESCOLA = 'escola';

    /** Nome dos cookies de sessão (separados por área). */
    private const NOME_ADMIN = 'SESS_EDUCA_ADMIN';
    private const NOME_ESCOLA = 'SESS_EDUCA_ESCOLA';

    /** Tempo de validade da sessão sem atividade (24 horas). */
    public const TEMPO_SESSAO = 86400;

    private static ?string $areaAtiva = null;

    /**
     * Descobre a área (escola ou admin) pela URL atual.
     * Permite manter sessões simultâneas entre os dois ambientes.
     */
    public static function areaDaRequisicao(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return str_starts_with($uri, '/admin') ? self::AREA_ADMIN : self::AREA_ESCOLA;
    }

    public static function nome(?string $area = null): string
    {
        $area = $area ?? self::AREA_ESCOLA;
        return $area === self::AREA_ADMIN ? self::NOME_ADMIN : self::NOME_ESCOLA;
    }

    /** Inicia a sessão da área (escola ou admin). */
    public static function iniciar(string $area): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        session_name(self::nome($area));
        ini_set('session.gc_maxlifetime', (string) self::TEMPO_SESSAO);
        session_set_cookie_params([
            'lifetime' => self::TEMPO_SESSAO,
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);

        self::$areaAtiva = $area;
        session_start();

        if (self::expirada()) {
            // Sessão antiga (mais de 24h sem atividade): derruba o login.
            session_unset();
            $_SESSION = [];
        }

        $_SESSION['last_activity'] = time();
    }

    public static function areaAtiva(): ?string
    {
        return self::$areaAtiva;
    }

    public static function expirada(): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        return (time() - (int) $_SESSION['last_activity']) > self::TEMPO_SESSAO;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerateId(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}