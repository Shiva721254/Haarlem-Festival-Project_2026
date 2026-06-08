<?php
namespace App\Models;

class ArtistModel
{
    public int $id;
    public string $name;
    public ?string $genre = null;
    public ?string $bio = null;
    public ?string $image = null;

    public static function fromDb(array $data): self
    {
        $a = new self();
        $a->id = (int)$data['id'];
        $a->name = $data['name'];
        $a->genre = $data['genre'] ?? null;
        $a->bio = $data['bio'] ?? null;
        $a->image = $data['image'] ?? null;
        return $a;
    }
}
