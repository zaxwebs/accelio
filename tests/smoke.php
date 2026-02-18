<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Support/helpers.php';

use Accelio\Core\Router;
use Accelio\Http\Request;
use Accelio\Http\Response;

$text = response('ok');
if (!$text instanceof Response || $text->status() !== 200 || $text->content() !== 'ok') {
    fwrite(STDERR, "response() helper failed\n");
    exit(1);
}

$json = json(['ok' => true], 201);
if ($json->status() !== 201) {
    fwrite(STDERR, "json() helper failed\n");
    exit(1);
}

if (created(['id' => 1])->status() !== 201) {
    fwrite(STDERR, "created() helper failed\n");
    exit(1);
}

if (no_content()->status() !== 204) {
    fwrite(STDERR, "no_content() helper failed\n");
    exit(1);
}

$router = new Router();
$router->get('/users/{id}', fn (Request $request): array => ['id' => $request->route('id')]);
$router->post('/echo', fn (Request $request): array => $request->body());

$requestWithParam = new Request('GET', '/users/42', [], [], [], [], '');
$routeResponse = $router->dispatch($requestWithParam);
if ($routeResponse->status() !== 200 || $routeResponse->content() !== '{"id":"42"}') {
    fwrite(STDERR, "route param dispatch failed\n");
    exit(1);
}

$jsonRequest = new Request('POST', '/echo', ['page' => 1], ['name' => 'Ada'], [], ['x-trace-id' => 'abc'], '');
if ($jsonRequest->all()['name'] !== 'Ada' || $jsonRequest->all()['page'] !== 1 || $jsonRequest->header('X-Trace-Id') !== 'abc') {
    fwrite(STDERR, "request helpers failed\n");
    exit(1);
}

$echoResponse = $router->dispatch($jsonRequest);
if ($echoResponse->content() !== '{"name":"Ada"}') {
    fwrite(STDERR, "post route failed\n");
    exit(1);
}

echo "smoke tests passed\n";
