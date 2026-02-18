<?php

declare(strict_types=1);

return [
    'name' => 'Accelio',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
    'timezone' => 'UTC',
];
