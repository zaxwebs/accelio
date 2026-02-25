<?php

declare(strict_types=1);

namespace Accelio\Core;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;

final class Container
{
    /** @var array<string, object|string> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $singletons = [];

    /** @var array<string, true> */
    private array $singletonIds = [];

    public function bind(string $id, object|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function singleton(string $id, object|string $concrete): void
    {
        $this->bind($id, $concrete);
        $this->singletonIds[$id] = true;

        if (is_object($concrete) && !($concrete instanceof Closure)) {
            $this->singletons[$id] = $concrete;
        }
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->singletons[$id]);
    }

    public function get(string $id): object
    {
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        $resolved = $this->resolve($id);

        if (isset($this->singletonIds[$id])) {
            $this->singletons[$id] = $resolved;
        }

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

        return $this->build($concrete);
    }

    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Service [$class] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Unable to resolve dependency [$%s] for service [%s].',
                $parameter->getName(),
                $class,
            ));
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
