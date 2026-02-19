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
