<?php

use Accelio\Http\Middleware;
use Accelio\Http\Pipeline;
use Accelio\Http\Request;
use Accelio\Http\Response;

test('pipeline executes handler without middleware', function () {
    $pipeline = new Pipeline();

    $request = Request::create('GET', '/test');
    $response = $pipeline->handle($request, fn () => Response::text('ok'));

    expect($response->content())->toBe('ok');
});

test('pipeline runs middleware in order', function () {
    $log = [];

    $first = new class ($log) implements Middleware {
        public function __construct(private array &$log) {}
        public function handle(Request $request, Closure $next): Response
        {
            $this->log[] = 'first:before';
            $response = $next($request);
            $this->log[] = 'first:after';
            return $response;
        }
    };

    $second = new class ($log) implements Middleware {
        public function __construct(private array &$log) {}
        public function handle(Request $request, Closure $next): Response
        {
            $this->log[] = 'second:before';
            $response = $next($request);
            $this->log[] = 'second:after';
            return $response;
        }
    };

    $pipeline = new Pipeline();
    $pipeline->pipe([$first, $second]);

    $request = Request::create('GET', '/test');
    $pipeline->handle($request, function () use (&$log) {
        $log[] = 'handler';
        return Response::text('done');
    });

    expect($log)->toBe(['first:before', 'second:before', 'handler', 'second:after', 'first:after']);
});

test('middleware can short-circuit the pipeline', function () {
    $blocker = new class implements Middleware {
        public function handle(Request $request, Closure $next): Response
        {
            return Response::text('blocked', 403);
        }
    };

    $pipeline = new Pipeline();
    $pipeline->pipe($blocker);

    $request = Request::create('GET', '/test');
    $response = $pipeline->handle($request, fn () => Response::text('should not reach'));

    expect($response->content())->toBe('blocked')
        ->and($response->status())->toBe(403);
});

test('middleware can modify response', function () {
    $headerAdder = new class implements Middleware {
        public function handle(Request $request, Closure $next): Response
        {
            $response = $next($request);
            return $response->withHeader('X-Custom', 'added');
        }
    };

    $pipeline = new Pipeline();
    $pipeline->pipe($headerAdder);

    $request = Request::create('GET', '/test');
    $response = $pipeline->handle($request, fn () => Response::text('ok'));

    expect($response->header('X-Custom'))->toBe('added');
});
