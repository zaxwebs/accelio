<?php

use Accelio\Http\Request;
use Accelio\Http\Response;

it('creates request from globals', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test?foo=bar';
    $_GET['foo'] = 'bar';

    $request = Request::capture();

    expect($request->method())->toBe('GET')
        ->and($request->path())->toBe('/test')
        ->and($request->query('foo'))->toBe('bar');
});

it('creates response', function () {
    $response = new Response('content', 201);

    expect($response->content())->toBe('content')
        ->and($response->status())->toBe(201);
});

it('creates json response', function () {
    $response = Response::json(['foo' => 'bar']);

    expect($response->content())->toBe('{"foo":"bar"}')
        ->and($response->header('Content-Type'))->toContain('application/json');
});

it('creates json response from objects', function () {
    $response = Response::json(new HttpTestJsonPayload('bar'));

    expect($response->content())->toBe('{"foo":"bar"}');
});

it('returns all input data when no key is provided', function () {
    $request = Request::create('POST', '/todos?filter=all', ['title' => 'Ship feature']);

    expect($request->input())->toBe([
        'filter' => 'all',
        'title' => 'Ship feature',
    ]);
});

final class HttpTestJsonPayload implements JsonSerializable
{
    public function __construct(private readonly string $foo) {}

    public function jsonSerialize(): mixed
    {
        return ['foo' => $this->foo];
    }
}
