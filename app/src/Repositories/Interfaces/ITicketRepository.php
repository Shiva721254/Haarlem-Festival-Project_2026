<?php
namespace App\Repositories\Interfaces;

interface ITicketRepository
{
    /**
     * Find one issued ticket by QR code, with customer and event details needed
     * by the scanner response.
     *
     * @return array<string,mixed>|null
     */
    public function findScanInfoByCode(string $code): ?array;

    public function markScanned(int $ticketId): void;
}
