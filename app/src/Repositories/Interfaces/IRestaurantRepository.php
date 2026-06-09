<?php
namespace App\Repositories\Interfaces;

use App\Models\RestaurantModel;

interface IRestaurantRepository
{
    /** @return RestaurantModel[] */
    public function getAll(): array;
    public function getById(int $id): ?RestaurantModel;
    public function create(RestaurantModel $restaurant): int;
    public function update(RestaurantModel $restaurant): void;
    public function delete(int $id): void;
}
