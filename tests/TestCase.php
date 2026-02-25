<?php

namespace Tests;

use Accelio\Core\Application;
use Accelio\Core\Kernel;
use Accelio\Http\Request;
use Accelio\Http\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

if (!defined('ACCELIO_TESTING')) {
    define('ACCELIO_TESTING', true);
}

abstract class TestCase extends BaseTestCase
{
    protected Application $app;
    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_SERVER['HTTP_REFERER'] = 'http://localhost/previous';

        $this->app = new Application(dirname(__DIR__));
        $this->kernel = new Kernel($this->app);
    }

    public function get(string $uri, array $headers = []): Response
    {
        return $this->call('GET', $uri, [], $headers);
    }

    public function post(string $uri, array $body = [], array $headers = []): Response
    {
        return $this->call('POST', $uri, $body, $headers);
    }

    public function put(string $uri, array $body = [], array $headers = []): Response
    {
        return $this->call('PUT', $uri, $body, $headers);
    }

    public function patch(string $uri, array $body = [], array $headers = []): Response
    {
        return $this->call('PATCH', $uri, $body, $headers);
    }

    public function delete(string $uri, array $headers = []): Response
    {
        return $this->call('DELETE', $uri, [], $headers);
    }

    public function call(string $method, string $uri, array $body = [], array $headers = []): Response
    {
        $request = Request::create($method, $uri, $body, $headers);

        return $this->kernel->handle($request);
    }
}
