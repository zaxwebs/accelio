<?php

declare(strict_types=1);

use Accelio\Core\Application;
use Accelio\Core\Kernel;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Support/helpers.php';

$app = new Application(basePath: dirname(__DIR__));
$kernel = new Kernel($app);

$response = $kernel->handle();
$response->send();
