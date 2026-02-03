<?php
namespace App\Services;

class RecaptchaService 
{
    private $secretKey = "6LcRjF8sAAAAAKP6omk4A3pM-mAqZcNmQKMiZCgN";

    public function verify($token) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query([
                    'secret'   => $this->secretKey,
                    'response' => $token
                ])
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);

        // Return true only if success is true and score is human-like (e.g., >= 0.5)
        return ($result->success && $result->score >= 0.5);
    }
}