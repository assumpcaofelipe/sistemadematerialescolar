<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, array $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Verifica rota exata
        if (isset($this->routes[$method][$uri])) {
            $this->call($this->routes[$method][$uri], []);
            return;
        }

        // Verifica rotas com parâmetros
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $this->call($handler, $params);
                    return;
                }
            }
        }

        http_response_code(404);
        require __DIR__ . '/../../views/404.php';
    }

    private function call(array $handler, array $params): void
    {
        $controllerClass = $handler[0];
        $method = $handler[1];

        foreach ($params as $chave => $valor) {
            if (is_string($valor) && is_numeric($valor)) {
                $params[$chave] = (int) $valor;
            }
        }

        $controller = new $controllerClass();
        call_user_func_array([$controller, $method], $params);
    }
}
