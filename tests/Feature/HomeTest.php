<?php

use Accelio\Http\Response;

test('home page returns 200', function () {
    $response = $this->get('/');

    expect($response->status())->toBe(200);
});

test('home page contains welcome message', function () {
    $response = $this->get('/');

    expect($response->content())->toContain('Accelio')
        ->and($response->content())->toContain('Lean PHP framework');
});

test('404 returns structured error', function () {
    $response = $this->get('/non-existent-page');

    expect($response->status())->toBe(404);

    $body = json_decode($response->content(), true);
    expect($body['ok'])->toBeFalse()
        ->and($body['error']['code'])->toBe('ROUTE_NOT_FOUND');
});

test('responses include trace id header', function () {
    $response = $this->get('/');

    expect($response->header('X-Trace-Id'))->not->toBeNull()
        ->and(strlen($response->header('X-Trace-Id')))->toBe(16);
});

test('responses include security headers', function () {
    $response = $this->get('/');

    expect($response->header('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->header('X-Frame-Options'))->toBe('DENY')
        ->and($response->header('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
});
