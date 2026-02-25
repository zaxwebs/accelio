<?php

use Accelio\Core\Container;
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

    $response = $this->router->dispatch($request);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->content())->toBe('Hello World');
});

test('it handles 404 with structured error', function () {
    $request = Request::create('GET', '/not-found');

    $response = $this->router->dispatch($request);

    expect($response->status())->toBe(404);

    $body = json_decode($response->content(), true);
    expect($body['ok'])->toBeFalse()
        ->and($body['error']['code'])->toBe('ROUTE_NOT_FOUND');
});

test('it handles route parameters', function () {
    $this->router->get('/user/{id}', fn ($id) => "User {$id}");

    $request = Request::create('GET', '/user/123');

    $response = $this->router->dispatch($request);

    expect($response->content())->toBe('User 123');
});

test('it returns 405 with allowed methods', function () {
    $this->router->post('/submit', fn () => 'ok');

    $request = Request::create('GET', '/submit');

    $response = $this->router->dispatch($request);

    expect($response->status())->toBe(405)
        ->and($response->header('Allow'))->toBe('POST');

    $body = json_decode($response->content(), true);
    expect($body['ok'])->toBeFalse()
        ->and($body['error']['code'])->toBe('METHOD_NOT_ALLOWED');
});

test('it handles multiple HTTP methods', function () {
    $this->router->get('/resource', fn () => 'get');
    $this->router->post('/resource', fn () => 'post');
    $this->router->put('/resource', fn () => 'put');
    $this->router->patch('/resource', fn () => 'patch');
    $this->router->delete('/resource', fn () => 'delete');

    $routes = $this->router->getRoutes();
    expect($routes)->toHaveKeys(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
});

test('it resolves class-string route handlers through the container', function () {
    $container = new Container();
    $router = new Router($container);

    $router->get('/todos', [TestTodoController::class, 'index']);

    $response = $router->dispatch(Request::create('GET', '/todos'));

    expect($response->content())->toBe('todos from service');
});

test('it resolves string controller action handlers', function () {
    $container = new Container();
    $router = new Router($container);

    $router->get('/todos/{id}', TestTodoController::class . '@show');

    $response = $router->dispatch(Request::create('GET', '/todos/15'));

    expect($response->content())->toBe('todo 15');
});

final class TestTodoController
{
    public function __construct(private readonly TestTodoService $service) {}

    public function index(): string
    {
        return $this->service->message();
    }

    public function show(string $id): string
    {
        return "todo {$id}";
    }
}

final class TestTodoService
{
    public function message(): string
    {
        return 'todos from service';
    }
}
