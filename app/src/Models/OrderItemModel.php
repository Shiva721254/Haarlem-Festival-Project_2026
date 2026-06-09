<?php
namespace App\Models;

class OrderItemModel
{
    public int $id;
    public int $order_id;
    public int $ticket_type_id;
    public int $quantity;
    public float $unit_price;
    public float $vat_rate;

    // Optional joined display data.
    public ?string $ticket_type_name = null;
    public ?string $event_title = null;

    public static function fromDb(array $data): self
    {
        $i = new self();
        $i->id = (int)$data['id'];
        $i->order_id = (int)$data['order_id'];
        $i->ticket_type_id = (int)$data['ticket_type_id'];
        $i->quantity = (int)$data['quantity'];
        $i->unit_price = (float)$data['unit_price'];
        $i->vat_rate = (float)$data['vat_rate'];
        $i->ticket_type_name = $data['ticket_type_name'] ?? null;
        $i->event_title = $data['event_title'] ?? null;
        return $i;
    }

    public function lineTotal(): float
    {
        return $this->unit_price * $this->quantity;
    }
}
