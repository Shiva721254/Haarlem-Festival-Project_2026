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

    // Mail. Defaults target the local MailHog container so development never
    // sends real email. Override via .env for a real SMTP provider.
    public static function mailHost(): string { return self::env('MAIL_HOST', 'mailhog'); }
    public static function mailPort(): string { return self::env('MAIL_PORT', '1025'); }
    public static function mailAuth(): bool { return filter_var(self::env('MAIL_AUTH', 'false'), FILTER_VALIDATE_BOOL); }
    public static function mailUser(): string { return self::env('MAIL_USER', ''); }
    public static function mailPass(): string { return self::env('MAIL_PASS', ''); }
    public static function mailSecure(): string { return self::env('MAIL_SECURE', ''); } // '', 'tls', or 'ssl'
    public static function mailFromEmail(): string { return self::env('MAIL_FROM_EMAIL', 'noreply@haarlemfestival.com'); }
    public static function mailFromName(): string { return self::env('MAIL_FROM_NAME', 'Haarlem Festival Support'); }

    // reCAPTCHA. When the keys are empty (e.g. local dev) captcha is treated as
    // disabled; set both in .env to enforce it in production.
    public static function recaptchaSiteKey(): string { return self::env('RECAPTCHA_SITE_KEY', ''); }
    public static function recaptchaSecretKey(): string { return self::env('RECAPTCHA_SECRET_KEY', ''); }
}
