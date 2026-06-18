<?php
namespace App\Services;

use App\Repositories\Interfaces\ITicketRepository;
use App\Services\Interfaces\ITicketScanService;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;

class TicketScanService implements ITicketScanService
{
    private ITicketRepository $ticketRepo;

    public function __construct(ITicketRepository $ticketRepo)
    {
        $this->ticketRepo = $ticketRepo;
    }

    public function scan(string $code): array
    {
        $code = trim($code);
        $ticket = $this->lookup($code);
        if (is_string($ticket)) {
            return $this->result(false, 'danger', $ticket, null);
        }
        return $this->rejection($ticket) ?? $this->accept($code, $ticket);
    }

    /** Find the ticket for a code, or return an error message string. */
    private function lookup(string $code): array|string
    {
        if ($code === '') {
            return 'Enter or scan a ticket code.';
        }
        return $this->ticketRepo->findScanInfoByCode($code) ?? 'Ticket not found.';
    }

    /** A rejection result if the ticket cannot be admitted, otherwise null. */
    private function rejection(array $ticket): ?array
    {
        if (($ticket['order_status'] ?? '') !== OrderStatus::Paid->value) {
            return $this->result(false, 'danger', 'Order is not paid. Do not admit this visitor.', $ticket);
        }
        if (($ticket['status'] ?? '') === TicketStatus::Scanned->value) {
            return $this->result(false, 'warning', 'Ticket was already scanned.', $ticket);
        }
        if (($ticket['status'] ?? '') !== TicketStatus::Valid->value) {
            return $this->result(false, 'danger', 'Ticket is not valid.', $ticket);
        }
        return null;
    }

    /** Mark the ticket scanned and return the acceptance result. */
    private function accept(string $code, array $ticket): array
    {
        $this->ticketRepo->markScanned((int)$ticket['id']);
        $ticket = $this->ticketRepo->findScanInfoByCode($code) ?? $ticket;
        return $this->result(true, 'success', 'Ticket accepted.', $ticket);
    }

    /**
     * @param array<string,mixed>|null $ticket
     * @return array{ok:bool,level:string,message:string,ticket:?array<string,mixed>}
     */
    private function result(bool $ok, string $level, string $message, ?array $ticket): array
    {
        return ['ok' => $ok, 'level' => $level, 'message' => $message, 'ticket' => $ticket];
    }
}
