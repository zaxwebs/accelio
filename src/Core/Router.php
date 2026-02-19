<?php

declare(strict_types=1);

namespace Accelio\Core;

use Accelio\Http\Request;
use Accelio\Http\Response;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

final class Router
{
    /** @var array<string, array<int, array{path: string, handler: callable}>> */
    private array $routes = [];

    /** @return array<string, array<int, array{path: string, handler: callable}>> */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = ['path' => $this->normalizePath($path), 'handler' => $handler];
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
            return Response::json([
                'error' => 'Method Not Allowed',
                'method' => $method,
                'path' => $path,
                'allowed' => $allowed,
            ], 405)->withHeader('Allow', implode(', ', $allowed));
        }

        return Response::json([
            'error' => 'Not Found',
            'method' => $method,
            'path' => $path,
        ], 404);
    }

    /**
     * @param array<string, string> $params
     */
    private function invokeHandler(callable $handler, Request $request, array $params): mixed
    {
        if (is_array($handler)) {
            $reflection = new ReflectionMethod($handler[0], $handler[1]);
        } else {
            $reflection = new ReflectionFunction($handler);
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

        return $handler(...$args);
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
