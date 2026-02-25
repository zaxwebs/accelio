<?php

declare(strict_types=1);

use Accelio\Http\Response;

if (!function_exists('response')) {
    function response(string $content, int $status = 200): Response
    {
        return Response::text($content, $status);
    }
}

if (!function_exists('json')) {
    /**
     * @param array<mixed> $payload
     */
    function json(array $payload, int $status = 200): Response
    {
        return Response::json($payload, $status);
    }
}

if (!function_exists('created')) {
    /**
     * @param array<mixed> $payload
     */
    function created(array $payload): Response
    {
        return Response::json($payload, 201);
    }
}

if (!function_exists('no_content')) {
    function no_content(): Response
    {
        return response('', 204);
    }
}

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(string $view, array $data = []): Response
    {
        $app = \Accelio\Core\Application::getInstance();
        $engine = new \Accelio\Core\View($app->basePath());

        return Response::html($engine->render($view, $data));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }
}

if (!function_exists('back')) {
    function back(int $status = 302): Response
    {
        $url = $_SERVER['HTTP_REFERER'] ?? '/';

        return redirect($url, $status);
    }
}

if (!function_exists('session')) {
    function session(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_SESSION;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities for safe output in views.
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get or generate the CSRF token for the current session.
     */
    function csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        $_SESSION['_csrf_token'] ??= bin2hex(random_bytes(32));

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a hidden input field containing the CSRF token.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}
