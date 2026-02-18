<?php

declare(strict_types=1);

namespace Accelio\Core;

use Closure;
use InvalidArgumentException;

final class Container
{
    /** @var array<string, Closure|object|string> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $singletons = [];

    public function bind(string $id, Closure|object|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function singleton(string $id, Closure|object|string $concrete): void
    {
        $this->bind($id, $concrete);

        if (is_object($concrete) && !($concrete instanceof Closure)) {
            $this->singletons[$id] = $concrete;
        }
    }

    public function get(string $id): object
    {
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        $resolved = $this->resolve($id);
        $this->singletons[$id] = $resolved;

        return $resolved;
    }

    public function make(string $id): object
    {
        return $this->resolve($id);
    }

    private function resolve(string $id): object
    {
        $concrete = $this->bindings[$id] ?? $id;

        if ($concrete instanceof Closure) {
            $instance = $concrete($this);

            if (!is_object($instance)) {
                throw new InvalidArgumentException("Service [$id] must resolve to an object.");
            }

            return $instance;
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        if (!class_exists($concrete)) {
            throw new InvalidArgumentException("Service [$id] is not instantiable.");
        }

        return new $concrete();
    }
}
