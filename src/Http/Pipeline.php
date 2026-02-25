<?php

declare(strict_types=1);

namespace Accelio\Http;

use Closure;

final class Pipeline
{
    /** @var list<Middleware> */
    private array $middleware = [];

    /**
     * @param list<Middleware>|Middleware $middleware
     */
    public function pipe(array|Middleware $middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = [...$this->middleware, ...$middleware];
        } else {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    /**
     * Run the request through the middleware stack and then the core handler.
     *
     * @param Closure(Request): Response $handler The core handler to execute after all middleware.
     */
    public function handle(Request $request, Closure $handler): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            static fn (Closure $next, Middleware $middleware): Closure =>
                static fn (Request $request): Response => $middleware->handle($request, $next),
            $handler,
        );

        return $pipeline($request);
    }
}
