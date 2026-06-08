<?php
namespace App\Models;

class RestaurantModel
{
    public int $id;
    public string $name;
    public ?string $cuisine = null;
    public ?string $description = null;
    public ?string $address = null;
    public ?int $stars = null;
    public ?float $price_per_seat = null;
    public ?string $image = null;

    public static function fromDb(array $data): self
    {
        $r = new self();
        $r->id = (int)$data['id'];
        $r->name = $data['name'];
        $r->cuisine = $data['cuisine'] ?? null;
        $r->description = $data['description'] ?? null;
        $r->address = $data['address'] ?? null;
        $r->stars = isset($data['stars']) ? (int)$data['stars'] : null;
        $r->price_per_seat = isset($data['price_per_seat']) ? (float)$data['price_per_seat'] : null;
        $r->image = $data['image'] ?? null;
        return $r;
    }
}
