<?php

declare(strict_types=1);

namespace Accelio\Core;

use Accelio\Error\ErrorCode;
use Accelio\Http\Middleware;
use Accelio\Http\Pipeline;
use Accelio\Http\Request;
use Accelio\Http\Response;
use Throwable;

final class Kernel
{
    private Router $router;
    private Pipeline $pipeline;

    /** @var array<string, string> */
    private const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '0',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    public function __construct(private readonly Application $app)
    {
        $this->router = new Router($app->container());
        $this->pipeline = new Pipeline();

        $app = $this->app;
        $router = $this->router;
        require $this->app->basePath('routes/web.php');
    }

    public function router(): Router
    {
        return $this->router;
    }

    /**
     * Register global middleware.
     *
     * @param list<Middleware>|Middleware $middleware
     */
    public function middleware(array|Middleware $middleware): self
    {
        $this->pipeline->pipe($middleware);

        return $this;
    }

    public function handle(?Request $request = null): Response
    {
        if (session_status() === PHP_SESSION_NONE && !defined('ACCELIO_TESTING')) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }

        $traceId = null;

        try {
            $request = $request ?? Request::capture();
            $traceId = $request->header('x-trace-id') ?? bin2hex(random_bytes(8));

            $response = $this->pipeline->handle(
                $request,
                fn (Request $req): Response => $this->router->dispatch($req),
            );

            return $this->applyStandardHeaders(
                $this->negotiateErrorResponse($request, $response),
                $traceId,
            );
        } catch (Throwable $throwable) {
            $request = $request ?? Request::capture();
            $error = Response::error(
                ErrorCode::InternalError,
                $this->app->config('debug') ? $throwable->getMessage() : 'Internal Server Error',
            );

            return $this->applyStandardHeaders(
                $this->negotiateErrorResponse($request, $error),
                $traceId ?? bin2hex(random_bytes(8)),
            );
        }
    }

    private function negotiateErrorResponse(Request $request, Response $response): Response
    {
        if ($response->status() < 400 || $request->wantsJson()) {
            return $response;
        }

        $body = json_decode($response->content(), true);
        $errorData = $body['error'] ?? [];

        $view = new View($this->app->basePath());
        $html = $view->render('errors.error', [
            'status' => $response->status(),
            'code' => $errorData['code'] ?? 'ERROR',
            'message' => $errorData['message'] ?? 'An unexpected error occurred.',
        ]);

        return Response::html($html, $response->status());
    }

    private function applyStandardHeaders(Response $response, string $traceId): Response
    {
        return $response->withHeaders([
            ...self::SECURITY_HEADERS,
            'X-Trace-Id' => $traceId,
        ]);
    }
}
