<?php

namespace App\Framework;

/**
 * Tiny helper for the redirect-and-stop pattern controllers repeat after every
 * POST. Centralises `header('Location: ...'); exit();` so actions stay short.
 */
class Redirect
{
    public static function to(string $path): never
    {
        header('Location: ' . $path);
        exit();
    }
}
