<?php

use Accelio\Core\View;

test('it renders a view with data', function () {
    $view = new View(dirname(__DIR__, 2));

    $output = $view->render('welcome', ['name' => 'TestApp']);

    expect($output)->toContain('TestApp')
        ->and($output)->toContain('Lean PHP framework');
});

test('it throws exception for missing view', function () {
    $view = new View(dirname(__DIR__, 2));

    expect(fn () => $view->render('nonexistent'))
        ->toThrow(RuntimeException::class, 'View [nonexistent] not found');
});

test('it prevents directory traversal', function () {
    $view = new View(dirname(__DIR__, 2));

    // Attempting to traverse should strip the ".." and fail gracefully
    expect(fn () => $view->render('../../config/app'))
        ->toThrow(RuntimeException::class);
});
