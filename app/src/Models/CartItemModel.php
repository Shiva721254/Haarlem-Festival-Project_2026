<?php
namespace App\Models;

/**
 * A line in the cart, enriched with the ticket type and its event so the cart
 * page can render without extra lookups.
 */
class CartItemModel
{
    public int $id;
    public int $cart_id;
    public int $ticket_type_id;
    public int $quantity;

    // Joined display data.
    public string $ticket_type_name;
    public float $price;
    public float $vat_rate;
    public int $event_id;
    public string $event_title;
    public int $available;     // remaining stock for this ticket type
    public ?string $special_requests = null;

    public static function fromDb(array $data): self
    {
        $i = new self();
        $i->id = (int)$data['id'];
        $i->cart_id = (int)$data['cart_id'];
        $i->ticket_type_id = (int)$data['ticket_type_id'];
        $i->quantity = (int)$data['quantity'];

        $i->ticket_type_name = $data['ticket_type_name'] ?? '';
        $i->price = (float)($data['price'] ?? 0);
        $i->vat_rate = (float)($data['vat_rate'] ?? 0);
        $i->event_id = (int)($data['event_id'] ?? 0);
        $i->event_title = $data['event_title'] ?? '';
        $i->available = (int)($data['available'] ?? 0);
        $i->special_requests = $data['special_requests'] ?? null;
        return $i;
    }

    public function lineSubtotal(): float
    {
        return $this->price * $this->quantity;
    }
}
