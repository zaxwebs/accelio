<?php

declare(strict_types=1);

namespace Accelio\Core;

final class Application
{
    private array $config;

    public function __construct(
        private readonly string $basePath,
        private readonly Container $container = new Container(),
    ) {
        $this->config = require $this->basePath . '/config/app.php';

        date_default_timezone_set($this->config['timezone'] ?? 'UTC');
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
