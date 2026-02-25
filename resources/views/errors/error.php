<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) $status) ?> — <?= e($code) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, 'DejaVu Sans Mono', monospace;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            color: #333;
            line-height: 1.5;
        }
        .error {
            text-align: center;
            padding: 2rem;
        }
        .error__status {
            font-size: 4rem;
            font-weight: 700;
            color: #111;
        }
        .error__code {
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            color: #999;
            margin-top: 0.25rem;
        }
        .error__message {
            margin-top: 1rem;
            color: #555;
        }
        .error__home {
            display: inline-block;
            margin-top: 1.5rem;
            color: #111;
            text-decoration: none;
            border-bottom: 1px solid #111;
        }
        .error__home:hover {
            color: #555;
            border-color: #555;
        }
    </style>
</head>
<body>
    <div class="error">
        <h1 class="error__status"><?= e((string) $status) ?></h1>
        <p class="error__code"><?= e($code) ?></p>
        <p class="error__message"><?= e($message) ?></p>
        <a href="/" class="error__home">← Back home</a>
    </div>
</body>
</html>
