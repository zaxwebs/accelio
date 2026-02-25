<?php

declare(strict_types=1);

namespace Accelio\Http;

use Accelio\Error\ErrorCode;

final class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $content,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'text/plain; charset=utf-8'],
    ) {}

    public static function text(string $content, int $status = 200): self
    {
        return new self($content, $status, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self($content, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            content: json_encode(self::normalizeJsonData($data), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            status: $status,
            headers: ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    /**
     * Create a structured error response using an ErrorCode enum.
     *
     * @param array<string, mixed> $details Additional context for the error.
     */
    public static function error(ErrorCode $code, string $message, array $details = []): self
    {
        $body = [
            'ok' => false,
            'error' => [
                'code' => $code->value,
                'message' => $message,
            ],
        ];

        if ($details !== []) {
            $body['error']['details'] = $details;
        }

        return self::json($body, $code->httpStatus());
    }

    private static function normalizeJsonData(mixed $data): mixed
    {
        if ($data instanceof \JsonSerializable) {
            return $data->jsonSerialize();
        }

        if (is_object($data)) {
            return get_object_vars($data);
        }

        return $data;
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self(
            content: '',
            status: $status,
            headers: ['Location' => $url],
        );
    }

    /**
     * Flash data to the session (useful for redirects).
     *
     * @param string|array<string, mixed> $key
     */
    public function with(string|array $key, mixed $value = null): self
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $_SESSION['_flash'][$k] = $v;
                }
            } else {
                $_SESSION['_flash'][$key] = $value;
            }
        }

        return $this;
    }

    public function withHeader(string $name, string $value): self
    {
        return new self($this->content, $this->status, [...$this->headers, $name => $value]);
    }

    /**
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        return new self($this->content, $this->status, [...$this->headers, ...$headers]);
    }

    public function withStatus(int $status): self
    {
        return new self($this->content, $status, $this->headers);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}
