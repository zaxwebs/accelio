<?php

declare(strict_types=1);

use Accelio\Http\Request;

$router->get('/', function (Request $request) use ($app) {
    return view('welcome', [
        'name' => $app->config('name'),
    ]);
});
