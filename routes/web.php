<?php

declare(strict_types=1);

use Accelio\Http\Request;

$router->get('/', function (Request $request) use ($app, $router) {
    $routes = [];
    foreach ($router->getRoutes() as $method => $methodRoutes) {
        foreach ($methodRoutes as $route) {
            if ($route['path'] !== '/') {
                $routes[] = [
                    'method' => $method,
                    'path' => $route['path'],
                ];
            }
        }
    }

    return view('welcome', [
        'name' => $app->config('name'),
        'routes' => $routes,
    ]);
});

$router->get('/api/health', fn () => json(['ok' => true]));

$router->get('/api/users/{id}', fn (Request $request, string $id) => json([
    'id' => $id,
    'trace' => $request->header('x-trace-id'),
]));

$router->post('/api/echo', fn (Request $request) => created([
    'received' => $request->body(),
]));

$router->delete('/api/sessions/{id}', fn () => no_content());

$router->get('/hello/{name}', fn (string $name) => view('hello', ['nickname' => $name]));

$router->post('/form', function (Request $request) {
    $nickname = $request->input('nickname');

    if (empty($nickname)) {
        $request->flash();

        return redirect('/')->with('message', 'Please provide a nickname!');
    }

    return redirect("/hello/{$nickname}");
});
