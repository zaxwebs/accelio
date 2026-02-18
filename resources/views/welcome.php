<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?></title>
    <style>
        body { font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, 'DejaVu Sans Mono', monospace; padding: 2rem; line-height: 1.5; color: #333; }
        .alert { color: green; margin-bottom: 1rem; }
        header { margin-bottom: 2rem; }
        section { margin-top: 2rem; border-top: 1px solid #ccc; padding-top: 1rem; }
    </style>
</head>
<body>
    <header>
        <h1><?= $name ?></h1>
        <p>Lean PHP framework. <a href="https://github.com/zaxwebs/accelio">Documentation</a></p>
    </header>


    <?php if (!empty($routes)): ?>
        <section>
            <h3>Available Routes</h3>
            <ul>
                <?php foreach ($routes as $route): ?>
                    <li>
                        <strong><?= $route['method'] ?></strong>
                        <?php if ($route['method'] === 'GET'): ?>
                            <a href="<?= $route['path'] ?>"><?= $route['path'] ?></a>
                        <?php else: ?>
                            <?= $route['path'] ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <section>
        <h3>Demo Form</h3>
        <?php if ($message = session('_flash_current')['message'] ?? null): ?>
            <p class="alert"><?= $message ?></p>
        <?php endif; ?>
        <form action="/form" method="POST">
            <input type="text" name="nickname" value="<?= old('nickname') ?>" placeholder="Nickname">
            <button type="submit">Submit</button>
        </form>
    </section>
</body>
</html>
