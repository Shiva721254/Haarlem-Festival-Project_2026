<?php
namespace App\Services;

use App\Config;

class RecaptchaService
{
    /**
     * Verify a reCAPTCHA v3 token.
     *
     * If no secret key is configured (typical for local development) captcha
     * is considered disabled and verification passes, so flows that require it
     * remain testable without real keys. Configure RECAPTCHA_SECRET_KEY in .env
     * to enforce it.
     */
    public function verify($token): bool
    {
        $secret = Config::recaptchaSecretKey();
        if ($secret === '') {
            return true; // captcha disabled in this environment
        }

        if (empty($token)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query([
                    'secret'   => $secret,
                    'response' => $token
                ])
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return false;
        }
        $result = json_decode($response);

        // Pass only when Google reports success and a human-like score.
        return isset($result->success, $result->score)
            && $result->success
            && $result->score >= 0.5;
    }
}
