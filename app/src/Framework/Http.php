<?php

namespace App\Framework;

/**
 * Small helpers for terminal HTTP responses, so controllers don't repeat the
 * status-code + echo + exit dance.
 */
class Http
{
    public static function notFound(string $message = 'Not found'): never
    {
        http_response_code(404);
        echo $message;
        exit();
    }
}
