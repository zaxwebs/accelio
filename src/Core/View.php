<?php

declare(strict_types=1);

namespace Accelio\Core;

use RuntimeException;
use Throwable;

final class View
{
    private readonly string $viewsPath;

    public function __construct(string $basePath)
    {
        $this->viewsPath = $basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->resolveViewPath($view);

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View [{$view}] not found at [{$viewPath}].");
        }

        return self::renderIsolated($viewPath, $data);
    }

    private function resolveViewPath(string $view): string
    {
        $normalized = str_replace(['..', "\0"], '', $view);

        return $this->viewsPath
            . DIRECTORY_SEPARATOR
            . str_replace('.', DIRECTORY_SEPARATOR, $normalized)
            . '.php';
    }

    private static function renderIsolated(string $__path, array $__data): string
    {
        extract($__data, EXTR_SKIP);

        ob_start();

        try {
            require $__path;
            return ob_get_clean() ?: '';
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
