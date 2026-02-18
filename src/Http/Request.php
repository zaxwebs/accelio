<?php

declare(strict_types=1);

namespace Accelio\Http;

final class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $routeParams
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $server,
        private readonly array $headers,
        private readonly string $rawBody,
        private array $routeParams = [],
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Robust URI detection for different server environments
        $uri = $_SERVER['PATH_INFO']
            ?? $_SERVER['REQUEST_URI']
            ?? '/';

        // Strip query string and ensure leading slash
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . ltrim($path, '/');

        // Remove index.php from path if it exists (e.g. from mod_rewrite or manual entry)
        $path = preg_replace('/^index\.php\/?/', '/', ltrim($path, '/'));
        $path = '/' . ltrim($path, '/');

        $rawBody = file_get_contents('php://input') ?: '';

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            body: self::captureBody($rawBody),
            server: $_SERVER,
            headers: self::captureHeaders(),
            rawBody: $rawBody,
        );
    }

    public function withRouteParams(array $routeParams): self
    {
        $clone = clone $this;
        $clone->routeParams = $routeParams;

        return $clone;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_replace($this->query, $this->body);
    }

    /**
     * @return array<string, mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * @return array<string, string>
     */
    private static function captureHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (!str_starts_with($name, 'HTTP_') || !is_string($value)) {
                continue;
            }

            $header = strtolower(str_replace('_', '-', substr($name, 5)));
            $headers[$header] = $value;
        }

        if (isset($_SERVER['CONTENT_TYPE']) && is_string($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * @return array<string, mixed>
     */
    private static function captureBody(string $rawBody): array
    {
        if ($_POST !== []) {
            return $_POST;
        }

        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($contentType, 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
