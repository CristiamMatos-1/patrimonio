<?php

namespace Core;

/**
 * Roteador MVC Básico
 */
class Router
{
    protected array $routes = [];

    /**
     * Adiciona uma rota
     */
    public function add(string $method, string $route, string $controllerAction): void
    {
        // Transforma a rota em regex para capturar parâmetros, se necessário futuramente
        $routeRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $routeRegex = '#^' . $routeRegex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $routeRegex,
            'action' => $controllerAction
        ];
    }

    /**
     * Despacha a requisição para o Controller correto
     */
    public function dispatch(string $method, string $url): void
    {
        $method = strtoupper($method);
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $url = '/' . trim($path, '/');
        if ($url === '//') {
            $url = '/';
        }

        foreach ($this->routes as $route) {
            $isMatch = preg_match($route['route'], $url, $matches);

            if ($route['method'] !== $method || !$isMatch) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            try {
                [$controllerName, $actionName] = explode('@', $route['action']);
                $controllerClass = "App\\Controllers\\" . $controllerName;

                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller não encontrado: {$controllerClass}");
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $actionName)) {
                    throw new \RuntimeException("Método não encontrado: {$controllerClass}@{$actionName}");
                }

                call_user_func_array([$controller, $actionName], $params);
                return;
            } catch (\Throwable $e) {
                error_log('Falha no dispatch: ' . $e->getMessage());
                http_response_code(500);
                echo "<h2>500 - Erro interno do servidor.</h2>";
                return;
            }
        }

        http_response_code(404);
        echo "<h2>404 - Página não encontrada.</h2>";
    }
}
