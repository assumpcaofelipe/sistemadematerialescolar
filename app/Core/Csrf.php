<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }

    public static function validate(?string $token = null): bool
    {
        $token = $token ?? ($_POST['csrf_token'] ?? null);
        return hash_equals(Session::get('csrf_token', ''), (string) $token);
    }
}