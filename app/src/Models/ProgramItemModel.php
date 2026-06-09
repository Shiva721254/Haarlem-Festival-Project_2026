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
        $p->event_id = (int)$data['event_id'];
        $p->title = $data['title'];
        $p->starts_at = $data['starts_at'];
        $p->ends_at = $data['ends_at'] ?? null;
        $p->image = $data['image'] ?? null;
        $p->venue_name = $data['venue_name'] ?? null;
        $p->type_slug = $data['type_slug'] ?? null;
        $p->type_name = $data['type_name'] ?? null;
        $p->ticket_types = $data['ticket_types'] ?? '';
        $p->total_tickets = (int)($data['total_tickets'] ?? 0);
        return $p;
    }
}
