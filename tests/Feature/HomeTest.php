<?php

use Accelio\Http\Response;

test('home page returns 200', function () {
    $response = $this->get('/');

    expect($response->status())->toBe(200);
});

test('home page contains welcome message', function () {
    $response = $this->get('/');

    expect($response->content())->toContain('Accelio');
});

test('404 page', function () {
    $response = $this->get('/non-existent-page');

    expect($response->status())->toBe(404);
});
