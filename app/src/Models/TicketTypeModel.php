<?php
namespace App\Models;

class TicketTypeModel
{
    public int $id;
    public int $event_id;
    public string $name;
    public float $price;
    public float $vat_rate;
    public int $capacity;
    public int $sold;
    public bool $is_active;

    public static function fromDb(array $data): self
    {
        $t = new self();
        $t->id = (int)$data['id'];
        $t->event_id = (int)$data['event_id'];
        $t->name = $data['name'];
        $t->price = (float)$data['price'];
        $t->vat_rate = (float)$data['vat_rate'];
        $t->capacity = (int)$data['capacity'];
        $t->sold = (int)$data['sold'];
        $t->is_active = (bool)$data['is_active'];
        return $t;
    }

    /**
     * Tickets still available to sell (capacity minus already sold).
     */
    public function available(): int
    {
        return max(0, $this->capacity - $this->sold);
    }

    public function isSoldOut(): bool
    {
        return $this->available() <= 0;
    }
}
