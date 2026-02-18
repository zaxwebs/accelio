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
