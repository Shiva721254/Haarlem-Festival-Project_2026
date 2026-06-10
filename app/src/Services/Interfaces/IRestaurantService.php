<?php
namespace App\Services\Interfaces;

use App\Models\RestaurantModel;

interface IRestaurantService
{
    /** @return RestaurantModel[] */
    public function getAll(): array;
    public function getById(int $id): ?RestaurantModel;
    /** @return array<int,array{id:int,title:string,starts_at:string,ends_at:?string}> */
    public function getSessions(int $restaurantId): array;
    public function create(RestaurantModel $restaurant): int;
    public function update(RestaurantModel $restaurant): void;
    public function delete(int $id): void;
}
