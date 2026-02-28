<?php
namespace App\Models;

use App\Enums\CuisineType;

class RestaurantModel
{
    public int $Id = 0;
    public string $Name = '';
    public string $Address = '';
    public CuisineType $CuisineType;
    public int $Stars = 0;
    public float $BasePrice = 0.0;
    public float $ReducedPrice = 0.0;
    public int $TotalSeats = 0;
    public string $ImagePath = '';

    public function __construct()
    {
        $this->CuisineType = CuisineType::from(''); 
    }

    public static function fromDb(array $data): self
    {
        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        $r = new self();

        $r->Id = (int)($data['id'] ?? 0);
        $r->Name = (string)($data['name'] ?? '');
        $r->Address = (string)($data['address'] ?? '');
        $r->CuisineType = $data['cuisine_type'] instanceof CuisineType
            ? $data['cuisine_type']
            : CuisineType::from($data['cuisine_type'] ?? '');
        $r->Stars = (int)($data['stars'] ?? 0);
        $r->BasePrice = (float)($data['base_price'] ?? 0);
        $r->ReducedPrice = (float)($data['reduced_price'] ?? 0);
        $r->TotalSeats = (int)($data['total_seats'] ?? 0);
        $r->ImagePath = (string)($data['image_path'] ?? '');

        return $r;
    }

    public function fromPost(): self
    {
        $r = self::fromArray($_POST);

        $this->Id = $r->Id;
        $this->Name = $r->Name;
        $this->Address = $r->Address;
        $this->CuisineType = $r->CuisineType;
        $this->Stars = $r->Stars;
        $this->BasePrice = $r->BasePrice;
        $this->ReducedPrice = $r->ReducedPrice;
        $this->TotalSeats = $r->TotalSeats;
        $this->ImagePath = $r->ImagePath;

        return $this;
    }
}
