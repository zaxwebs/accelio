<?php

declare(strict_types=1);

namespace Accelio\Error;

enum ErrorCode: string
{
    case RouteNotFound = 'ROUTE_NOT_FOUND';
    case MethodNotAllowed = 'METHOD_NOT_ALLOWED';
    case ValidationFailed = 'VALIDATION_FAILED';
    case InternalError = 'INTERNAL_ERROR';
    case Unauthorized = 'UNAUTHORIZED';
    case Forbidden = 'FORBIDDEN';
    case RateLimited = 'RATE_LIMITED';
    case CsrfMismatch = 'CSRF_MISMATCH';

    /**
     * Default HTTP status code for this error.
     */
    public function httpStatus(): int
    {
        return match ($this) {
            self::RouteNotFound => 404,
            self::MethodNotAllowed => 405,
            self::ValidationFailed => 422,
            self::InternalError => 500,
            self::Unauthorized => 401,
            self::Forbidden => 403,
            self::RateLimited => 429,
            self::CsrfMismatch => 419,
        };
    }
}
