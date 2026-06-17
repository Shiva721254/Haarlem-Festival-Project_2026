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
    public ?string $special_requests = null;

    // Optional joined display data.
    public ?string $ticket_type_name = null;
    public ?string $event_title = null;

    public static function fromDb(array $data): self
    {
        $i = new self();
        $i->fillLine($data);
        $i->fillDisplay($data);
        return $i;
    }

    private function fillLine(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->order_id = (int)$data['order_id'];
        $this->ticket_type_id = (int)$data['ticket_type_id'];
        $this->quantity = (int)$data['quantity'];
        $this->unit_price = (float)$data['unit_price'];
        $this->vat_rate = (float)$data['vat_rate'];
    }

    private function fillDisplay(array $data): void
    {
        $this->special_requests = $data['special_requests'] ?? null;
        $this->ticket_type_name = $data['ticket_type_name'] ?? null;
        $this->event_title = $data['event_title'] ?? null;
    }

    public function lineTotal(): float
    {
        return $this->unit_price * $this->quantity;
    }
}
