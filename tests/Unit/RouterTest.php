<?php

use Accelio\Core\Router;
use Accelio\Http\Request;
use Accelio\Http\Response;

beforeEach(function () {
    $this->router = new Router();
});

test('it registers GET routes', function () {
    $this->router->get('/test', fn () => 'response');

    $routes = $this->router->getRoutes();
    expect($routes['GET'])->toHaveCount(1)
        ->and($routes['GET'][0]['path'])->toBe('/test');
});

test('it dispatches to a handler', function () {
    $this->router->get('/hello', fn () => 'Hello World');

    $request = Request::create('GET', '/hello');
    // $request->shouldReceive('withRouteParams')->andReturnSelf(); // Not needed if using real object
    
    $response = $this->router->dispatch($request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->content())->toBe('Hello World');
});

test('it handles 404', function () {
    $request = Request::create('GET', '/not-found');

    $response = $this->router->dispatch($request);

    expect($response->status())->toBe(404);
});

test('it handles route parameters', function () {
    $this->router->get('/user/{id}', fn ($id) => "User {$id}");

    $request = Request::create('GET', '/user/123');

    $response = $this->router->dispatch($request);

    expect($response->content())->toBe('User 123');
});
