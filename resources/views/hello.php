<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello <?= $nickname ?></title>
    <style>
        body { font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, 'DejaVu Sans Mono', monospace; padding: 2rem; line-height: 1.5; color: #333; }
        footer { margin-top: 2rem; border-top: 1px solid #ccc; padding-top: 1rem; }
    </style>
</head>
<body>
    <h1>Hello, <?= $nickname ?>!</h1>
    <p>Nice to meet you.</p>

    <footer>
        <p><a href="/">Back to home</a></p>
    </footer>
</body>
</html>
