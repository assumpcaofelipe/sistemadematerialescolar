<?php

declare(strict_types=1);

namespace App\Core;

class StatusPedido
{
    public const REALIZADO = 'realizado';
    public const EM_ANDAMENTO = 'em_andamento';
    public const CONCLUIDO = 'concluido';
    public const CANCELADO = 'cancelado';

    public const ROTULOS = [
        self::REALIZADO    => 'Novo pedido',
        self::EM_ANDAMENTO => 'Em andamento',
        self::CONCLUIDO    => 'Concluído',
        self::CANCELADO    => 'Cancelado',
    ];

    public const ICONES = [
        self::REALIZADO    => 'circle-yellow',
        self::EM_ANDAMENTO => 'circle-blue',
        self::CONCLUIDO    => 'check-circle',
        self::CANCELADO    => 'circle-red',
    ];

    public const CORES = [
        self::REALIZADO    => 'warning',
        self::EM_ANDAMENTO => 'primary',
        self::CONCLUIDO    => 'success',
        self::CANCELADO    => 'danger',
    ];

    public static function rotulo(string $status): string
    {
        return self::ROTULOS[$status] ?? $status;
    }

    public static function cor(string $status): string
    {
        return self::CORES[$status] ?? 'secondary';
    }

    public static function icone(string $status): string
    {
        return self::ICONES[$status] ?? '';
    }

    public static function todos(): array
    {
        return array_keys(self::ROTULOS);
    }
}