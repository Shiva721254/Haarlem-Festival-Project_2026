<?php
namespace App\Services;

use App\Models\RestaurantModel;
use App\Framework\ImageUpload;
use App\Repositories\Interfaces\IRestaurantRepository;
use App\Services\Interfaces\IRestaurantService;

class RestaurantService implements IRestaurantService
{
    private IRestaurantRepository $repo;

    public function __construct(IRestaurantRepository $repo)
    {
        $this->repo = $repo;
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

    /** @return array<int,array{id:int,title:string,starts_at:string,ends_at:?string}> */
    public function getSessions(int $restaurantId): array
    {
        return $this->repo->getSessions($restaurantId);
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

    public function buildAdminFormModel(array $post): array
    {
        $restaurant = $this->hydrate($post);
        $image = ImageUpload::resolve('image_file', 'restaurants');
        if ($image['path'] !== null) {
            $restaurant->image = $image['path'];
        }
        return ['restaurant' => $restaurant, 'error' => $this->validateAdminForm($restaurant), 'uploadError' => $image['error']];
    }

    private function hydrate(array $post): RestaurantModel
    {
        $restaurant = new RestaurantModel();
        $restaurant->id = (int)($post['id'] ?? 0);
        $restaurant->name = trim($post['name'] ?? '');
        $restaurant->cuisine = trim($post['cuisine'] ?? '') ?: null;
        $restaurant->description = trim($post['description'] ?? '') ?: null;
        $restaurant->address = trim($post['address'] ?? '') ?: null;
        $restaurant->stars = ($post['stars'] ?? '') !== '' ? (int)$post['stars'] : null;
        $restaurant->price_per_seat = ($post['price_per_seat'] ?? '') !== '' ? (float)$post['price_per_seat'] : null;
        $restaurant->image = trim($post['image'] ?? '') ?: null;
        return $restaurant;
    }

    public function validateAdminForm(RestaurantModel $restaurant): ?string
    {
        if ($restaurant->name === '') {
            return 'Restaurant name is required.';
        }
        if ($restaurant->stars !== null && ($restaurant->stars < 0 || $restaurant->stars > 5)) {
            return 'Stars must be between 0 and 5.';
        }
        if ($restaurant->price_per_seat !== null && $restaurant->price_per_seat < 0) {
            return 'Price per seat cannot be negative.';
        }
        return null;
    }
}
