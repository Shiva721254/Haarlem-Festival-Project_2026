<?php
namespace App\Services\Interfaces;

interface IMailService
{
    public function send(string $to, string $subject, string $body): bool;

    /**
     * @param array<int,array{path:string,name:string}> $attachments
     */
    public function sendWithAttachments(string $to, string $subject, string $body, array $attachments): bool;
}
