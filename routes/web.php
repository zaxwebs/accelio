<?php

declare(strict_types=1);

use Accelio\Http\Request;

$router->get('/', function (Request $request) use ($app) {
    return json([
        'framework' => $app->config('name'),
        'message' => 'Lean PHP framework for AI-assisted development.',
        'path' => $request->path(),
        'query' => $request->all(),
    ]);
});

$router->get('/health', fn () => json(['ok' => true]));

$router->get('/users/{id}', fn (Request $request) => json([
    'id' => $request->route('id'),
    'trace' => $request->header('x-trace-id'),
]));

$router->post('/echo', fn (Request $request) => created([
    'received' => $request->body(),
]));

$router->delete('/sessions/{id}', fn (Request $request) => no_content());
