<?php
namespace App;

final class Config
{
    private static function env(string $key, ?string $default = null): string
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            if ($default !== null) return $default;
            throw new \RuntimeException("Missing env var: {$key}");
        }
        return (string)$val;
    }

    public static function dbHost(): string { return self::env('DB_HOST'); }
    public static function dbPort(): string { return self::env('DB_PORT', '3306'); }
    public static function dbName(): string { return self::env('DB_NAME'); }
    public static function dbUser(): string { return self::env('DB_USER'); }
    public static function dbPass(): string { return self::env('DB_PASS'); }
}
