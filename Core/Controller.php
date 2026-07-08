<?php

namespace Core;

/**
 * Controller Base
 * Fornece métodos utilitários para os Controllers da aplicação.
 */
abstract class Controller
{
    /**
     * Renderiza uma view.
     * @param string $view Caminho da view (ex: 'home/index')
     * @param array $data Dados a serem passados para a view
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';
        $viewFileFallback = BASE_PATH . '/app/views/' . $view . '.php';

        // Tenta descobrir onde está o layout principal para passar para as views
        $layoutPath = BASE_PATH . '/app/Views/layouts/main.php';
        if (!file_exists($layoutPath)) {
            $layoutPath = BASE_PATH . '/app/views/layouts/main.php';
        }
        $data['layoutPath'] = $layoutPath;

        // Extrai as variáveis para que fiquem disponíveis na view
        extract($data);

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } elseif (file_exists($viewFileFallback)) {
            require_once $viewFileFallback;
        } else {
            die("View {$viewFile} (ou {$viewFileFallback}) não encontrada.");
        }
    }

    /**
     * Redireciona para uma rota específica
     */
    protected function redirect(string $url): void
    {
        header("Location: " . APP_URL . $url);
        exit;
    }

    /**
     * Retorna uma resposta JSON (útil para requisições AJAX/APIs)
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
