# Accelio

Accelio is a **modern, lean PHP framework** optimized for **AI-assisted development**:

- **Small surface area** so LLMs can reason across the whole codebase quickly.
- **Convention-first folder layout** with explicit class names.
- **No magic**: route handlers are plain callables, services are explicit, and config is simple arrays.
- **Type-safe signatures** and docblocks that are easy for code models to infer.

## Why this is LLM-friendly

1. Predictable file paths (`routes/web.php`, `config/app.php`, `src/Http/*`).
2. Minimal indirection (single `Kernel` + `Router` + `Container`).
3. Copy/paste-ready patterns for routes, middleware, and services.
4. Simple JSON/text responses for API-first workflows.

## Quick start

```bash
composer install
composer serve
```

Open <http://127.0.0.1:8080>.

## Project structure

```txt
config/
  app.php
public/
  index.php
routes/
  web.php
src/
  Core/
    Application.php
    Container.php
    Kernel.php
    Router.php
  Http/
    Request.php
    Response.php
  Support/
    helpers.php
```

## Add a route

Edit `routes/web.php`:

```php
$router->get('/hello', fn (Request $request) => response('Hello from Accelio!'));
```

## Add a service

In `public/index.php`:

```php
$app->container()->singleton(MyService::class, fn () => new MyService());
```

Use it in route:

```php
$router->get('/version', function (Request $request) use ($app) {
    $service = $app->container()->get(MyService::class);
    return response($service->version());
});
```

## AI workflow tips

- Ask your coding assistant to "create one route and one service using existing conventions".
- Keep features in small files with one class each.
- Prefer named classes for production code and closures for quick prototypes.

## License

MIT
