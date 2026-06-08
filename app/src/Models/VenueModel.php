<?php
namespace App\Models;

class VenueModel
{
    public int $id;
    public string $name;
    public ?string $address = null;
    public ?int $capacity = null;
    public ?string $description = null;
    public ?string $image = null;

    public static function fromDb(array $data): self
    {
        $v = new self();
        $v->id = (int)$data['id'];
        $v->name = $data['name'];
        $v->address = $data['address'] ?? null;
        $v->capacity = isset($data['capacity']) ? (int)$data['capacity'] : null;
        $v->description = $data['description'] ?? null;
        $v->image = $data['image'] ?? null;
        return $v;
    }
}
