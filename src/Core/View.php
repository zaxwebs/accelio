<?php

declare(strict_types=1);

namespace Accelio\Core;

use Exception;

final class View
{
    private string $viewsPath;

    public function __construct(string $basePath)
    {
        $this->viewsPath = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->viewsPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View [{$view}] not found at [{$viewPath}].");
        }

        extract($data);

        ob_start();

        require $viewPath;

        return ob_get_clean() ?: '';
    }
}
