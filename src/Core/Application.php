<?php

declare(strict_types=1);

namespace Accelio\Core;

final class Application
{
    private static ?Application $instance = null;
    private readonly Config $config;

    public function __construct(
        private readonly string $basePath,
        private readonly Container $container = new Container(),
    ) {
        self::$instance = $this;

        $raw = require $this->basePath . '/config/app.php';
        $this->config = Config::fromArray($raw);

        date_default_timezone_set($this->config->timezone);
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

    /**
     * Retrieve a configuration value by key.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }

    /**
     * Access the typed Config object directly.
     */
    public function configuration(): Config
    {
        return $this->config;
    }
}
