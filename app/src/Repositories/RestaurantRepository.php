<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\RestaurantModel;
use App\Repositories\Interfaces\IRestaurantRepository;

class RestaurantRepository extends Repository implements IRestaurantRepository
{
    /** @return RestaurantModel[] */
    public function getAll(): array
    {
        $rows = $this->fetchAll('SELECT * FROM restaurants ORDER BY name');
        return array_map(static fn(array $row) => RestaurantModel::fromDb($row), $rows);
    }

    public function getById(int $id): ?RestaurantModel
    {
        $row = $this->fetchOne('SELECT * FROM restaurants WHERE id = :id', ['id' => $id]);
        return $row ? RestaurantModel::fromDb($row) : null;
    }

    /**
     * Published reservation sessions for a restaurant (its Yummy events).
     *
     * @return array<int,array{id:int,title:string,starts_at:string,ends_at:?string}>
     */
    public function getSessions(int $restaurantId): array
    {
        return $this->fetchAll(
            'SELECT id, title, starts_at, ends_at
             FROM events
             WHERE restaurant_id = :id AND is_published = 1
             ORDER BY starts_at',
            ['id' => $restaurantId]
        );
    }

    public function create(RestaurantModel $restaurant): int
    {
        $sql = 'INSERT INTO restaurants (name, cuisine, description, address, stars, price_per_seat, image)
                VALUES (:name, :cuisine, :description, :address, :stars, :price_per_seat, :image)';
        $this->execute($sql, $this->params($restaurant));
        return $this->lastInsertId();
    }

    public function update(RestaurantModel $restaurant): void
    {
        $params = $this->params($restaurant);
        $params['id'] = $restaurant->id;
        $this->execute(
            'UPDATE restaurants SET name = :name, cuisine = :cuisine, description = :description,
                address = :address, stars = :stars, price_per_seat = :price_per_seat, image = :image
             WHERE id = :id',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM restaurants WHERE id = :id', ['id' => $id]);
    }

    /** @return array<string,mixed> */
    private function params(RestaurantModel $restaurant): array
    {
        return [
            'name' => $restaurant->name,
            'cuisine' => $restaurant->cuisine,
            'description' => $restaurant->description,
            'address' => $restaurant->address,
            'stars' => $restaurant->stars,
            'price_per_seat' => $restaurant->price_per_seat,
            'image' => $restaurant->image,
        ];
    }
}
