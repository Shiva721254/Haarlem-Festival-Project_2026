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
    public ?float $custom_price = null;   // donation amount or discounted price

    public static function fromDb(array $data): self
    {
        $i = new self();
        $i->fillCartLine($data);
        $i->fillTicketData($data);
        return $i;
    }

    private function fillCartLine(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->cart_id = (int)$data['cart_id'];
        $this->ticket_type_id = (int)$data['ticket_type_id'];
        $this->quantity = (int)$data['quantity'];
        $this->special_requests = $data['special_requests'] ?? null;
        $this->custom_price = isset($data['custom_price']) ? (float)$data['custom_price'] : null;
    }

    private function fillTicketData(array $data): void
    {
        $this->ticket_type_name = $data['ticket_type_name'] ?? '';
        $this->price = (float)($data['price'] ?? 0);
        $this->vat_rate = (float)($data['vat_rate'] ?? 0);
        $this->event_id = (int)($data['event_id'] ?? 0);
        $this->event_title = $data['event_title'] ?? '';
        $this->available = (int)($data['available'] ?? 0);
    }

    /** The price actually charged for this line (custom price overrides the base). */
    public function effectivePrice(): float
    {
        return $this->custom_price ?? $this->price;
    }

    public function lineSubtotal(): float
    {
        return $this->effectivePrice() * $this->quantity;
    }
}
