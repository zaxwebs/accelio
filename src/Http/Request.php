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
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['_old'] = $_SESSION['_flash_input'] ?? [];
            unset($_SESSION['_flash_input']);

            $_SESSION['_flash_current'] = $_SESSION['_flash'] ?? [];
            unset($_SESSION['_flash']);
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $headers = self::captureHeaders();
        $query = $_GET;
        $body = self::captureBody($rawBody, $headers);

        return new self(
            method: self::resolveMethod($body, $headers),
            path: self::resolvePath(),
            query: $query,
            body: $body,
            server: $_SERVER,
            headers: $headers,
            rawBody: $rawBody,
        );
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public static function create(string $method, string $uri, array $body = [], array $headers = []): self
    {
        $components = parse_url($uri);
        $path = $components['path'] ?? '/';
        $query = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        return new self(
            method: strtoupper($method),
            path: $path,
            query: $query,
            body: $body,
            server: [],
            headers: $headers,
            rawBody: '',
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

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->all();
        }

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

    public function ip(): string
    {
        return (string) ($this->header('x-forwarded-for') ?: $this->server('REMOTE_ADDR', '0.0.0.0'));
    }

    public function wantsJson(): bool
    {
        return str_contains(strtolower((string) $this->header('accept', '')), 'application/json');
    }

    public function bearerToken(): ?string
    {
        $authorization = (string) $this->header('authorization', '');

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    public function session(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_SESSION;
        }

        return $_SESSION[$key] ?? $default;
    }

    public function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }

    public function flashData(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_SESSION['_flash_current'] ?? [];
        }

        return $_SESSION['_flash_current'][$key] ?? $default;
    }

    public function flash(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['_flash_input'] = $this->all();
        }
    }

    private static function resolvePath(): string
    {
        $uri = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $path = '/' . ltrim($path, '/');
        $path = (string) preg_replace('#^/index\.php/?#', '/', $path);

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * @param array<string, string> $headers
     */
    private static function resolveMethod(array $body, array $headers): string
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method !== 'POST') {
            return $method;
        }

        $override = $body['_method']
            ?? $headers['x-http-method-override']
            ?? null;

        if (!is_string($override) || $override === '') {
            return $method;
        }

        return strtoupper($override);
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

        foreach (['CONTENT_TYPE' => 'content-type', 'CONTENT_LENGTH' => 'content-length'] as $serverKey => $headerKey) {
            if (isset($_SERVER[$serverKey]) && is_string($_SERVER[$serverKey])) {
                $headers[$headerKey] = $_SERVER[$serverKey];
            }
        }

        return $headers;
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    private static function captureBody(string $rawBody, array $headers): array
    {
        if ($_POST !== []) {
            return $_POST;
        }

        $contentType = strtolower((string) ($headers['content-type'] ?? ''));

        if (str_contains($contentType, 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded') && $rawBody !== '') {
            parse_str($rawBody, $parsed);
            return is_array($parsed) ? $parsed : [];
        }

        return [];
    }
}
