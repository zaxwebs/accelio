<?php

declare(strict_types=1);

namespace Accelio\Core;

final class Application
{
    private static ?Application $instance = null;
    private array $config;

    public function __construct(
        private readonly string $basePath,
        private readonly Container $container = new Container(),
    ) {
        self::$instance = $this;
        $this->config = require $this->basePath . '/config/app.php';

        date_default_timezone_set($this->config['timezone'] ?? 'UTC');
    }

    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application not initialized.');
        }

        return self::$instance;
    }

    public function basePath(string $path = ''): string
    {
        return rtrim($this->basePath . '/' . ltrim($path, '/'), '/');
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
