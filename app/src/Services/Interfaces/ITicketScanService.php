<?php
namespace App\Services\Interfaces;

interface ITicketScanService
{
    /**
     * Scan a QR code and return a scanner-friendly result.
     *
     * @return array{ok:bool,level:string,message:string,ticket:?array<string,mixed>}
     */
    public function scan(string $code): array;
}
