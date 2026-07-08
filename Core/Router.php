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
        
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove de forma segura qualquer caminho base (como /patrimonio ou subpastas adicionais)
        // Isso resolve o problema independentemente de onde o script está rodando
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $url = str_replace($scriptName, '', $requestUri);
        } else {
            $url = $requestUri;
        }
        
        // Garante que a URL comece com /
        $url = '/' . ltrim($url, '/');

        // Se após limpar ficou vazio, é a página inicial
        if ($url === '' || $url === '//') {
            $url = '/';
        }

        // Adicionando um array de log para debug profundo
        $debugLog = [];
        $debugLog[] = "Iniciando dispatch para URL tratada: $url (Método: $method)";

        foreach ($this->routes as $route) {
            $isMatch = preg_match($route['route'], $url, $matches);
            
            if ($route['method'] === $method && $isMatch) {
                $debugLog[] = "Match ENCONTRADO para a regra: {$route['route']}";
                
                // Filtra apenas os parâmetros nomeados capturados via Regex
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerName, $actionName] = explode('@', $route['action']);
                $controllerClass = "App\\Controllers\\" . $controllerName;

                $debugLog[] = "Tentando instanciar: $controllerClass";

                // --- INÍCIO DO BYPASS DO AUTOLOADER ---
                // Vamos calcular o caminho físico do arquivo do Controller e dar um require explícito
                // Espera encontrar os Controllers dentro da pasta app/Controllers (ou app/controllers, caso-sensitive扱)
$relativeClassPath = 'Controllers/' . str_replace('\\', '/', $controllerName) . '.php';
                
                // --- NOVA ABORDAGEM: SCAN DINÂMICO DE DIRETÓRIOS ---
                // Em vez de adivinhar maiúsculas e minúsculas, vamos ler a pasta física
                // e encontrar o arquivo ignorando case-sensitivity completamente.
                
                $parts = explode('/', ltrim($relativeClassPath, '/'));
                $currentPath = rtrim(BASE_PATH, '/') . '/';
                
                // Primeiro vamos garantir a pasta app
                $appFound = false;
                foreach (scandir($currentPath) as $dir) {
                    if (strtolower($dir) === 'app') {
                        $currentPath .= $dir . '/';
                        $appFound = true;
                        break;
                    }
                }
                
                if ($appFound) {
                    $fileLoaded = false;
                    
                    foreach ($parts as $index => $part) {
                        $isLast = ($index === count($parts) - 1); // É o arquivo .php?
                        $foundMatch = false;
                        
                        if (is_dir($currentPath)) {
                            $items = scandir($currentPath);
                            foreach ($items as $item) {
                                if ($item === '.' || $item === '..') continue;
                                
                                if (strtolower($item) === strtolower($part)) {
                                    $currentPath .= $item;
                                    $currentPath .= $isLast ? '' : '/';
                                    $foundMatch = true;
                                    break;
                                }
                            }
                        }
                        
                        if (!$foundMatch) {
                            $debugLog[] = "ERRO: Não conseguiu encontrar a parte '" . $part . "' no diretório " . $currentPath;
                            break;
                        }
                        
                        if ($isLast && $foundMatch) {
                            require_once $currentPath;
                            $fileLoaded = true;
                            $debugLog[] = "Arquivo carregado via SCAN DINÂMICO: " . $currentPath;
                        }
                    }
                }

                if (!isset($fileLoaded) || !$fileLoaded) {
                    $debugLog[] = "ERRO FATAL: O arquivo físico não foi encontrado no servidor mesmo usando Scan Dinâmico.";
                    $debugLog[] = "O PHP procurou fisicamente pelo Controller: " . $controllerName;
                    $debugLog[] = "Certifique-se de que o arquivo realmente foi enviado para o cPanel e está dentro da pasta app/Controllers/";
                }
                // --- FIM DO BYPASS ---

                if (class_exists($controllerClass)) {
                    $debugLog[] = "Classe $controllerClass existe!";
                    $controller = new $controllerClass();
                    if (method_exists($controller, $actionName)) {
                        call_user_func_array([$controller, $actionName], $params);
                        return; // Sucesso, finaliza a execução
                    } else {
                        $debugLog[] = "ERRO: Método $actionName não existe na classe $controllerClass.";
                    }
                } else {
                    $debugLog[] = "ERRO: Classe $controllerClass NÃO FOI ENCONTRADA pelo Autoloader.";
                }
            }
        }

        // Se não encontrou a rota, exibe o 404 e o log de debug detalhado
        http_response_code(404);
        echo "<h2>404 - Página não encontrada.</h2>";
        echo "<h3>Log de Debug Profundo:</h3>";
        echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; font-size:12px;'>";
        echo implode("\n", $debugLog);
        echo "</pre>";
    }
}
