<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService 
{
    private array $config;

    public function __construct() 
    {
        // Ideally, move these to a config file or .env
        $this->config = [
            'host' => 'smtp.gmail.com', // Or your SMTP provider
            'auth' => true,
            'username' => 'davidkutejx21@gmail.com',
            'password' => 'nycj ehjj pxdf brkb',
            'secure' => PHPMailer::ENCRYPTION_STARTTLS,
            'port' => 587,
            'from_email' => 'noreply@webstore.com',
            'from_name' => 'Webstore Support'
        ];
    }

    public function send(string $to, string $subject, string $body): bool 
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = $this->config['auth'];
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = $this->config['secure'];
            $mail->Port       = $this->config['port'];

            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body); // Plain text version
            // Add this inside the try block for testings
            $mail->SMTPDebug = 0; // set to 0 in production

            return $mail->send();
        } catch (Exception $e) {
            // Log error: $mail->ErrorInfo
            return false;
        }
    }
}