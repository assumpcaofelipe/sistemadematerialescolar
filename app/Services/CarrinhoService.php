<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\Produto;

class CarrinhoService
{
    private const CHAVE = 'carrinho';

    public static function obter(): array
    {
        return Session::get(self::CHAVE, []);
    }

    public static function adicionar(int $produtoId, int $quantidade): void
    {
        if ($quantidade <= 0) {
            return;
        }

        $carrinho = self::obter();
        $carrinho[$produtoId] = ($carrinho[$produtoId] ?? 0) + $quantidade;
        Session::set(self::CHAVE, $carrinho);
    }

    public static function atualizar(int $produtoId, int $quantidade): void
    {
        $carrinho = self::obter();
        if ($quantidade <= 0) {
            unset($carrinho[$produtoId]);
        } else {
            $carrinho[$produtoId] = $quantidade;
        }
        Session::set(self::CHAVE, $carrinho);
    }

    public static function remover(int $produtoId): void
    {
        $carrinho = self::obter();
        unset($carrinho[$produtoId]);
        Session::set(self::CHAVE, $carrinho);
    }

    public static function limpar(): void
    {
        Session::remove(self::CHAVE);
    }

    public static function totalItens(): int
    {
        return array_sum(self::obter());
    }

    public static function vazio(): bool
    {
        return self::totalItens() === 0;
    }

    /**
     * Retorna os itens do carrinho com dados do produto.
     *
     * @return array<int, array{produto: array, quantidade: int}>
     */
    public static function itensDetalhados(): array
    {
        $carrinho = self::obter();
        if (!$carrinho) {
            return [];
        }

        $produtos = (new Produto())->findMany(array_keys($carrinho));
        $itens = [];

        foreach ($produtos as $produto) {
            $id = (int) $produto['id'];
            if (!isset($carrinho[$id])) {
                continue;
            }
            $itens[] = [
                'produto'    => $produto,
                'quantidade' => (int) $carrinho[$id],
            ];
        }

        return $itens;
    }
}