<?php
namespace App\Services;

use App\Config;
use App\Services\Interfaces\IMailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService implements IMailService
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
        try {
            $mail = new PHPMailer(true);
            $this->configureTransport($mail);
            $this->compose($mail, $to, $subject, $body, $attachments);
            return $mail->send();
        } catch (Exception $e) {
            return false; // $mail->ErrorInfo holds the detail
        }
    }

    /** Set sender, recipient, in-memory attachments and the HTML/plain body. */
    private function compose(PHPMailer $mail, string $to, string $subject, string $body, array $attachments): void
    {
        $mail->setFrom(Config::mailFromEmail(), Config::mailFromName());
        $mail->addAddress($to);
        foreach ($attachments as $att) {
            $mail->addStringAttachment($att['content'], $att['name'], PHPMailer::ENCODING_BASE64, $att['type']);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // plain-text fallback
    }

    /** Apply SMTP transport settings (env-driven; defaults to the local MailHog container). */
    private function configureTransport(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host     = Config::mailHost();
        $mail->Port     = (int) Config::mailPort();
        $mail->SMTPAuth = Config::mailAuth();
        if (Config::mailAuth()) {
            $mail->Username = Config::mailUser();
            $mail->Password = Config::mailPass();
        }
        $this->applyEncryption($mail, Config::mailSecure());
    }

    private function applyEncryption(PHPMailer $mail, string $secure): void
    {
        if ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            // MailHog speaks plain SMTP with no encryption.
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }
    }
}
