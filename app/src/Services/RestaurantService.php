<?php
namespace App\Services;

use App\Interfaces\IRestaurantRepository;
use App\Interfaces\IRestaurantService;
use App\Models\RestaurantModel;
use App\Repositories\RestaurantRepository;

class RestaurantService implements IRestaurantService
{
    private IRestaurantRepository $repo;

    public function __construct(?IRestaurantRepository $repo = null)
    {
        $this->repo = $repo ?? new RestaurantRepository();
    }

    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): ?RestaurantModel
    {
        return $this->repo->getById($id);
    }

    public function create(RestaurantModel $restaurant): int
    {
        $restaurant->Name = trim($restaurant->Name);
        $restaurant->Address = trim($restaurant->Address);
        $restaurant->ImagePath = trim($restaurant->ImagePath);

        return $this->repo->create($restaurant);
    }

    public function update(RestaurantModel $restaurant): void
    {
        $restaurant->Name = trim($restaurant->Name);
        $restaurant->Address = trim($restaurant->Address);
        $restaurant->ImagePath = trim($restaurant->ImagePath);

        $this->repo->update($restaurant);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}
