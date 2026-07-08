<?php

declare(strict_types=1);

namespace App\Lib;

final class View
{
    public static function render(array $config, string $template, array $data = []): void
    {
        $templatePath = __DIR__ . '/../Views/' . $template . '.php';
        if (!is_file($templatePath)) {
            $templatePathAlt = __DIR__ . '/../views/' . $template . '.php';
            if (is_file($templatePathAlt)) {
                $templatePath = $templatePathAlt;
            }
        }

        $layoutPath = __DIR__ . '/../Views/layout.php';
        if (!is_file($layoutPath)) {
            $layoutPathAlt = __DIR__ . '/../views/layout.php';
            if (is_file($layoutPathAlt)) {
                $layoutPath = $layoutPathAlt;
            }
        }

        if (!is_file($templatePath) || !is_file($layoutPath)) {
            http_response_code(500);
            echo 'Template não encontrado';
            return;
        }

        $data['config'] = $config;
        $data['flash'] = Flash::consume();
        $data['csrf_token'] = Csrf::token();
        $data['current_user'] = Auth::user();

        extract($data, EXTR_SKIP);

        require $layoutPath;
    }
}
