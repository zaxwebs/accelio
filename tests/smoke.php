<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Support/helpers.php';

use Accelio\Http\Response;

$text = response('ok');
if (!$text instanceof Response || $text->status() !== 200 || $text->content() !== 'ok') {
    fwrite(STDERR, "response() helper failed\n");
    exit(1);
}

$json = json(['ok' => true], 201);
if ($json->status() !== 201) {
    fwrite(STDERR, "json() helper failed\n");
    exit(1);
}

echo "smoke tests passed\n";
