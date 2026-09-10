<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }

        require $viewPath;
    }

    /**
     * Renderiza uma view do ambiente escola, injetando os dados
     * necessários ao layout (usuário logado e total do carrinho).
     */
    protected function viewEscola(string $view, array $data = []): void
    {
        $data['usuario'] = Auth::user();
        $data['escolaCarrinhoItens'] = \App\Services\CarrinhoService::totalItens();
        $this->view($view, $data);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function old(string $key, $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    protected function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
