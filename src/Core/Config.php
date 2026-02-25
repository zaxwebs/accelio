<?php

declare(strict_types=1);

namespace Accelio\Core;

final readonly class Config
{
    public function __construct(
        public string $name,
        public Environment $env,
        public bool $debug,
        public string $timezone,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? 'Accelio'),
            env: Environment::tryFrom((string) ($data['env'] ?? 'local')) ?? Environment::Local,
            debug: ((string) ($data['debug'] ?? 'false')) === 'true' || $data['debug'] === true,
            timezone: (string) ($data['timezone'] ?? 'UTC'),
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'name' => $this->name,
            'env' => $this->env->value,
            'debug' => $this->debug,
            'timezone' => $this->timezone,
            default => $default,
        };
    }
}
