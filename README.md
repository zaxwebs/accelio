# Accelio

Accelio is a **modern, lean PHP framework** optimized for **AI-assisted development**, now supporting both **API-first** and **traditional web development** workflows.

- **Small surface area** so LLMs can reason across the whole codebase quickly.
- **Convention-first folder layout** with explicit class names.
- **No magic**: route handlers are plain callables, services are explicit, and config is simple arrays.
- **Type-safe signatures** and docblocks that are easy for code models to infer.
- **Built for modern PHP** with a PHP `^8.4` runtime baseline.

## Why this is LLM-friendly

1. Predictable file paths (`routes/web.php`, `config/app.php`, `src/Http/*`, `resources/views/*`).
2. Minimal indirection (single `Kernel` + `Router` + `Container`).
3. Copy/paste-ready patterns for routes, services, and views.
4. Built-in session and view rendering support.

## Quick start

```bash
composer install
php -S localhost:8000 -t public
```

Open <http://localhost:8000>.

## Project structure

```txt
config/
  app.php
public/
  index.php
resources/
  views/          <-- PHP templates
routes/
  web.php
src/
  Core/
    Application.php
    Container.php
    Kernel.php
    Router.php
    View.php      <-- Template engine
  Http/
    Request.php   <-- Session & input helpers
    Response.php  <-- Redirect & HTML helpers
  Support/
    helpers.php
```

## Features included

- **Traditional Web**: View rendering, global sessions, and PRG (Post-Redirect-Get) support.
- **API-First**: JSON body parsing, route parameters, and header lookups.
- **Route params**: `/users/{id}` then either `$request->route('id')` or typed closure args (`fn (string $id) => ...`).
- **Query + body merge**: Access via `$request->all()` or `$request->input('key')`.
- **Response helpers**: `json()`, `view()`, `redirect()`, `back()`, `created()`, and `no_content()`.
- **HTTP verbs**: `GET`, `POST`, `PUT`, `PATCH`, and `DELETE` with automatic `405 Method Not Allowed` responses.

## Common Examples

### Rendering a View

Create `resources/views/welcome.php`:
```php
<h1>Welcome to <?= $name ?></h1>
```

Edit `routes/web.php`:
```php
$router->get('/', fn () => view('welcome', ['name' => 'Accelio']));
```

### Form Handling & Redirects (PRG)

```php
$router->post('/submit', function (Request $request) {
    if (!$request->input('name')) {
        $request->flash(); // Persist input for old()
        return redirect('/')->with('message', 'Name required!');
    }
    return redirect('/hello/' . $request->input('name'));
});

$router->get('/hello/{name}', fn (Request $request) => view('hello', [
    'nickname' => $request->route('name')
]));
```

### JSON API

```php
$router->get('/api/health', fn () => json(['ok' => true]));

$router->post('/api/echo', fn (Request $request) => created([
    'received' => $request->body(),
]));
```

## AI workflow tips

- Ask your coding assistant to "create a route and a view using existing helpers".
- Keep features in small files with one class each.
- Use `Request $request` type hinting in route closures for better autocompletion.

## License

MIT
