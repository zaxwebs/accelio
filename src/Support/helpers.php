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
