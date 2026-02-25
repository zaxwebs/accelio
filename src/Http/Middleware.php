<?php

declare(strict_types=1);

namespace Accelio\Http;

use Closure;

interface Middleware
{
    public function handle(Request $request, Closure $next): Response;
}
