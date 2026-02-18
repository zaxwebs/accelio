<?php

declare(strict_types=1);

namespace Accelio\Core;

use Accelio\Http\Request;
use Accelio\Http\Response;
use Throwable;

final class Kernel
{
    private Router $router;

    public function __construct(private readonly Application $app)
    {
        $this->router = new Router();

        $app = $this->app;
        $router = $this->router;
        require $this->app->basePath('routes/web.php');
    }

    public function handle(): Response
    {
        try {
            $request = Request::capture();
            return $this->router->dispatch($request);
        } catch (Throwable $throwable) {
            $payload = ['error' => 'Server Error'];

            if ((bool) $this->app->config('debug', false) === true) {
                $payload['message'] = $throwable->getMessage();
            }

            return Response::json($payload, 500);
        }
    }
}
