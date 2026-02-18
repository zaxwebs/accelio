<?php

declare(strict_types=1);

namespace Accelio\Core;

use Accelio\Http\Request;
use Accelio\Http\Response;

final class Router
{
    /** @var array<string, array<int, array{path: string, handler: callable}>> */
    private array $routes = [];

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
        $this->routes[$method][] = ['path' => $path, 'handler' => $handler];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            $result = ($route['handler'])($request->withRouteParams($params));

            if ($result instanceof Response) {
                return $result;
            }

            if (is_array($result)) {
                return Response::json($result);
            }

            return Response::text((string) $result);
        }

        return Response::json([
            'error' => 'Not Found',
            'method' => $method,
            'path' => $path,
        ], 404);
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

        if (!preg_match('#^' . $pattern . '$#', $requestPath, $matches)) {
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
}
