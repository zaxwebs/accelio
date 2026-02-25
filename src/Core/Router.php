<?php

declare(strict_types=1);

namespace Accelio\Core;

use Accelio\Error\ErrorCode;
use Accelio\Http\Method;
use Accelio\Http\Request;
use Accelio\Http\Response;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

final class Router
{
    /** @var array<string, array<int, array{path: string, handler: callable|array|string}>> */
    private array $routes = [];

    public function __construct(private readonly ?Container $container = null) {}

    /** @return array<string, array<int, array{path: string, handler: callable|array|string}>> */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function get(string $path, callable|array|string $handler): void
    {
        $this->add(Method::Get, $path, $handler);
    }

    public function post(string $path, callable|array|string $handler): void
    {
        $this->add(Method::Post, $path, $handler);
    }

    public function put(string $path, callable|array|string $handler): void
    {
        $this->add(Method::Put, $path, $handler);
    }

    public function patch(string $path, callable|array|string $handler): void
    {
        $this->add(Method::Patch, $path, $handler);
    }

    public function delete(string $path, callable|array|string $handler): void
    {
        $this->add(Method::Delete, $path, $handler);
    }

    public function add(Method|string $method, string $path, callable|array|string $handler): void
    {
        $key = $method instanceof Method ? $method->value : strtoupper($method);
        $this->routes[$key][] = ['path' => $this->normalizePath($path), 'handler' => $handler];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            return $this->normalizeResponse(
                $this->invokeHandler($route['handler'], $request->withRouteParams($params), $params),
            );
        }

        $allowed = $this->allowedMethodsForPath($path);
        if ($allowed !== []) {
            return Response::error(
                ErrorCode::MethodNotAllowed,
                "Method {$method} is not allowed for {$path}. Allowed: " . implode(', ', $allowed),
            )->withHeader('Allow', implode(', ', $allowed));
        }

        return Response::error(
            ErrorCode::RouteNotFound,
            "No route matches {$method} {$path}.",
        );
    }

    /**
     * @param array<string, string> $params
     */
    private function invokeHandler(callable|array|string $handler, Request $request, array $params): mixed
    {
        $resolved = $this->resolveHandler($handler);

        if (is_array($resolved)) {
            $reflection = new ReflectionMethod($resolved[0], $resolved[1]);
        } else {
            $reflection = new ReflectionFunction($resolved);
        }

        $args = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $type->getName() === Request::class) {
                $args[] = $request;
                continue;
            }

            $name = $parameter->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            $args[] = null;
        }

        return $resolved(...$args);
    }

    private function resolveHandler(callable|array|string $handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $handler = [$class, $method];
        }

        if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && is_string($handler[1])) {
            $instance = $this->container?->get($handler[0]) ?? new $handler[0]();
            return [$instance, $handler[1]];
        }

        throw new \InvalidArgumentException('Route handler is not callable.');
    }

    private function normalizeResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::text((string) $result);
    }

    /**
     * @return array<int, string>
     */
    private function allowedMethodsForPath(string $path): array
    {
        $allowed = [];

        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                if ($this->match($route['path'], $path) !== null) {
                    $allowed[] = $method;
                    break;
                }
            }
        }

        sort($allowed);

        return $allowed;
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $routePath, string $requestPath): ?array
    {
        if ($routePath === $requestPath) {
            return [];
        }

        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $routePath,
        );

        if (!is_string($pattern)) {
            return null;
        }

        $escaped = preg_replace('/\//', '\\/', $pattern);
        if (!is_string($escaped)) {
            return null;
        }

        if (!preg_match('/^' . $escaped . '$/', $requestPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = urldecode($value);
            }
        }

        return $params;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . ltrim($path, '/');

        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized;
    }
}
