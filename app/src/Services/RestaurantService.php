<?php
namespace App\Services;

use App\Models\RestaurantModel;
use App\Repositories\Interfaces\IRestaurantRepository;
use App\Repositories\RestaurantRepository;
use App\Services\Interfaces\IRestaurantService;

class RestaurantService implements IRestaurantService
{
    private IRestaurantRepository $repo;

    public function __construct()
    {
        $this->repo = new RestaurantRepository();
    }

    /** @return RestaurantModel[] */
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
        return $this->repo->create($restaurant);
    }

    public function update(RestaurantModel $restaurant): void
    {
        $this->repo->update($restaurant);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}
