# Accelio

Accelio is a lightweight PHP framework designed for teams building web and API applications with AI-assisted workflows.

It emphasizes predictable structure, explicit behavior, and small abstractions so both developers and coding agents can understand and extend code quickly.

## Highlights

- **AI-friendly architecture**: low indirection and explicit conventions reduce reasoning overhead for LLM-based tooling.
- **Modern PHP baseline**: built for **PHP 8.3+**.
- **Dual delivery model**: supports both traditional server-rendered pages and JSON APIs.
- **Practical HTTP helpers**: ergonomic request/response APIs for forms, redirects, sessions, and APIs.
- **Minimal core**: clear separation between routing, container, kernel, request, and response layers.

## Best fit

Use Accelio when you want:

- A clean starting point for internal tools, MVPs, and API backends.
- Full control over application flow without heavy framework magic.
- A repository shape that is easy for humans and AI agents to navigate.

## Requirements

- PHP `^8.3`
- Composer
- Pest PHP (for testing)

## Testing

Run the test suite with:

```bash
composer test
```



## Quick start

```bash
composer install
php -S localhost:8000 -t public
```

Then open <http://localhost:8000>.

## Project layout

```txt
config/
  app.php
public/
  index.php
resources/
  views/
routes/
  web.php
src/
  Core/
    Application.php
    Container.php
    Kernel.php
    Router.php
    View.php
  Http/
    Request.php
    Response.php
  Support/
    helpers.php
tests/
```

## Core capabilities

### Routing

- Supports `GET`, `POST`, `PUT`, `PATCH`, and `DELETE`.
- Provides automatic `405 Method Not Allowed` responses when a route exists for a different method.
- Supports route parameters such as `/users/{id}` with access via route bindings or typed closure arguments.

### Request handling

- Query string and request body accessors.
- JSON request body parsing for API endpoints.
- Session-backed input flashing for post/redirect/get workflows.

### Response helpers

Built-in helpers include:

- `json()`
- `view()`
- `redirect()`
- `back()`
- `created()`
- `no_content()`


## Development workflow recommendations

To keep Accelio optimized for AI-assisted implementation tasks:

1. Keep features small and focused (one concern per class/file).
2. Prefer explicit dependencies and typed signatures.
3. Reuse existing helpers before introducing new abstractions.
4. Keep routing intent readable and colocate related view/API handlers.

## License

MIT
