<?php

declare(strict_types=1);

namespace Accelio\Core;

enum Environment: string
{
    case Local = 'local';
    case Staging = 'staging';
    case Production = 'production';
    case Testing = 'testing';

    public function isProduction(): bool
    {
        return $this === self::Production;
    }

    public function isLocal(): bool
    {
        return $this === self::Local;
    }
}
