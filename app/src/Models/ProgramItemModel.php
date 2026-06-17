<?php
namespace App\Models;

/**
 * One entry in a customer's personal program: an event they hold ticket(s) for.
 */
class ProgramItemModel
{
    public int $event_id;
    public string $title;
    public string $starts_at;
    public ?string $ends_at = null;
    public ?string $image = null;
    public ?string $venue_name = null;
    public ?string $type_slug = null;
    public ?string $type_name = null;
    public string $ticket_types = '';   // comma-separated names
    public int $total_tickets = 0;

    public static function fromDb(array $data): self
    {
        $p = new self();
        $p->fillEvent($data);
        $p->fillTicketSummary($data);
        return $p;
    }

    private function fillEvent(array $data): void
    {
        $this->event_id = (int)$data['event_id'];
        $this->title = $data['title'];
        $this->starts_at = $data['starts_at'];
        $this->ends_at = $data['ends_at'] ?? null;
        $this->image = $data['image'] ?? null;
    }

    private function fillTicketSummary(array $data): void
    {
        $this->venue_name = $data['venue_name'] ?? null;
        $this->type_slug = $data['type_slug'] ?? null;
        $this->type_name = $data['type_name'] ?? null;
        $this->ticket_types = $data['ticket_types'] ?? '';
        $this->total_tickets = (int)($data['total_tickets'] ?? 0);
    }
}
