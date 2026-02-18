<?php

declare(strict_types=1);

use Accelio\Http\Request;

$router->get('/', function (Request $request) use ($app) {
    return json([
        'framework' => $app->config('name'),
        'message' => 'Lean PHP framework for AI-assisted development.',
        'path' => $request->path(),
    ]);
});

$router->get('/health', fn () => json(['ok' => true]));
