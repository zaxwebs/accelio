# Accelio

A lightweight PHP 8.3+ framework for web and API apps, designed for both developers and AI agents.

Predictable structure, explicit behavior, small abstractions — easy to understand and extend for humans and coding agents alike.

## Quick Start

```bash
composer install
php -S localhost:8000 -t public
```

Then open <http://localhost:8000>. Run tests with `composer test`.

## Project Layout

```
src/
  Core/          Application, Config, Container, Environment, Kernel, Router, View
  Http/          Request, Response, Method, Middleware, Pipeline
  Error/         ErrorCode
  Support/       helpers.php
config/app.php   App configuration
routes/web.php   Route definitions
resources/views/ PHP templates
public/index.php Entrypoint
tests/           Pest test suite
```

## Architecture

| File | Role |
|------|------|
| `Application.php` | Bootstraps typed `Config`, container, kernel |
| `Config.php` | Readonly value object with `Environment` enum |
| `Kernel.php` | Request lifecycle, middleware pipeline, trace IDs, security headers |
| `Router.php` | Route registration (`Method` enum), path params, structured 404/405 |
| `Container.php` | DI container — `bind`, `singleton`, `make`, `has` |
| `View.php` | Template rendering, isolated scope, directory traversal prevention |
| `Request.php` | Query/body/session/route params, flash helpers, bearer token |
| `Response.php` | HTML/JSON/redirect/error factories |
| `Method.php` | HTTP method enum (GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS) |
| `Middleware.php` | Middleware interface (`handle` + `$next` pattern) |
| `Pipeline.php` | Chains middleware around a core handler |
| `ErrorCode.php` | Machine-readable error codes with HTTP status mapping |
| `helpers.php` | `view`, `json`, `redirect`, `back`, `created`, `no_content`, `e`, `csrf_token`, `csrf_field` |

## Routing

```php
$router->get('/users/{id}', function (Request $request, $id) {
    return json(['id' => $id]);
});
```

Supports `GET`, `POST`, `PUT`, `PATCH`, `DELETE`. Returns structured `405` with `Allow` header when method is wrong.

## Error Responses

All errors use a consistent envelope:

```json
{"ok": false, "error": {"code": "ROUTE_NOT_FOUND", "message": "No route matches GET /path."}}
```

Codes: `ROUTE_NOT_FOUND`, `METHOD_NOT_ALLOWED`, `VALIDATION_FAILED`, `INTERNAL_ERROR`, `UNAUTHORIZED`, `FORBIDDEN`, `RATE_LIMITED`, `CSRF_MISMATCH`.

## Middleware

```php
$kernel->middleware(new MyMiddleware());
```

Implement `Accelio\Http\Middleware` with `handle(Request $request, Closure $next): Response`.

## Typical Tasks

**Add a page:** create `resources/views/mypage.php`, then `$router->get('/mypage', fn() => view('mypage'))`.

**Add a JSON endpoint:** `$router->post('/api/items', fn(Request $r) => created(['id' => 1]))`.

**Add middleware:** create a class implementing `Middleware`, register via `$kernel->middleware(...)`.

## Conventions

- One concern per file. Keep handlers readable in `routes/web.php`.
- Use `e($value)` in templates for XSS-safe output.
- Use `Response::error(ErrorCode::case, 'message')` for structured errors.
- Prefer helpers (`json`, `created`, `redirect`, `back`) for consistency.
- All responses automatically include `X-Trace-Id` and security headers.
- PHP 8.3 baseline — use strict types, enums, readonly classes.

## Requirements

- PHP `^8.3`, Composer, Pest PHP (dev)

## License

MIT
