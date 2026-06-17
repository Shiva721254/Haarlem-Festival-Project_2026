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
    public bool $is_donation = false;       // pay-what-you-like

    // Joined when loaded with its event (e.g. for HaarlemPas eligibility).
    public ?string $event_type_slug = null;

    public static function fromDb(array $data): self
    {
        $t = new self();
        $t->fillIdentity($data);
        $t->fillSales($data);
        return $t;
    }

    private function fillIdentity(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->event_id = (int)$data['event_id'];
        $this->name = $data['name'];
        $this->event_type_slug = $data['event_type_slug'] ?? null;
    }

    private function fillSales(array $data): void
    {
        $this->price = (float)$data['price'];
        $this->vat_rate = (float)$data['vat_rate'];
        $this->capacity = (int)$data['capacity'];
        $this->sold = (int)$data['sold'];
        $this->is_active = (bool)$data['is_active'];
        $this->is_donation = (bool)($data['is_donation'] ?? false);
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
