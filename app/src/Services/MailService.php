<?php
namespace App\Services;

use App\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        return $this->sendWithAttachments($to, $subject, $body, []);
    }

    /**
     * Send an HTML email with optional in-memory attachments.
     *
     * @param array<int,array{name:string,content:string,type:string}> $attachments
     */
    public function sendWithAttachments(string $to, string $subject, string $body, array $attachments): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings (env-driven; defaults to the local MailHog container)
            $mail->isSMTP();
            $mail->Host       = Config::mailHost();
            $mail->Port       = (int) Config::mailPort();
            $mail->SMTPAuth   = Config::mailAuth();

            if (Config::mailAuth()) {
                $mail->Username = Config::mailUser();
                $mail->Password = Config::mailPass();
            }

            $secure = Config::mailSecure();
            if ($secure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($secure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                // MailHog speaks plain SMTP with no encryption.
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }

            // Recipients
            $mail->setFrom(Config::mailFromEmail(), Config::mailFromName());
            $mail->addAddress($to);

            // In-memory attachments (e.g. ticket / invoice PDFs)
            foreach ($attachments as $att) {
                $mail->addStringAttachment($att['content'], $att['name'], PHPMailer::ENCODING_BASE64, $att['type']);
            }

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
