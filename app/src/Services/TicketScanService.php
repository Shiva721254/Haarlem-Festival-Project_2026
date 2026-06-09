<?php
namespace App\Services;

use App\Repositories\Interfaces\ITicketRepository;
use App\Repositories\TicketRepository;
use App\Services\Interfaces\ITicketScanService;

class TicketScanService implements ITicketScanService
{
    private ITicketRepository $ticketRepo;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
    }

    public function scan(string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            return $this->result(false, 'danger', 'Enter or scan a ticket code.', null);
        }

        $ticket = $this->ticketRepo->findScanInfoByCode($code);
        if ($ticket === null) {
            return $this->result(false, 'danger', 'Ticket not found.', null);
        }

        if (($ticket['order_status'] ?? '') !== 'paid') {
            return $this->result(false, 'danger', 'Order is not paid. Do not admit this visitor.', $ticket);
        }

        if (($ticket['status'] ?? '') === 'scanned') {
            return $this->result(false, 'warning', 'Ticket was already scanned.', $ticket);
        }

        if (($ticket['status'] ?? '') !== 'valid') {
            return $this->result(false, 'danger', 'Ticket is not valid.', $ticket);
        }

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
