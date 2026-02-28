<?php
namespace App\Repositories;

use App\Interfaces\IRestaurantRepository;
use App\Models\RestaurantModel;
use App\Core\Database;
use PDO;

class RestaurantRepository implements IRestaurantRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT id, name, address, cuisine_type, stars, base_price, reduced_price, total_seats, image_path
                FROM restaurants
                ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $result[] = RestaurantModel::fromDb($row);
        }

        return $result;
    }

    public function getById(int $id): ?RestaurantModel
    {
        $sql = "SELECT id, name, address, cuisine_type, stars, base_price, reduced_price, total_seats, image_path
                FROM restaurants
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? RestaurantModel::fromDb($row) : null;
    }

    public function create(RestaurantModel $restaurant): int
    {
        $sql = "INSERT INTO restaurants (name, address, cuisine_type, stars, base_price, reduced_price, total_seats, image_path)
                VALUES (:name, :address, :cuisine_type, :stars, :base_price, :reduced_price, :total_seats, :image_path)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $restaurant->Name,
            ':address' => $restaurant->Address,
            ':cuisine_type' => $restaurant->CuisineType->value,
            ':stars' => $restaurant->Stars,
            ':base_price' => $restaurant->BasePrice,
            ':reduced_price' => $restaurant->ReducedPrice,
            ':total_seats' => $restaurant->TotalSeats,
            ':image_path' => $restaurant->ImagePath,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(RestaurantModel $restaurant): void
    {
        $sql = "UPDATE restaurants
                SET name = :name,
                    address = :address,
                    cuisine_type = :cuisine_type,
                    stars = :stars,
                    base_price = :base_price,
                    reduced_price = :reduced_price,
                    total_seats = :total_seats,
                    image_path = :image_path
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $restaurant->Name,
            ':address' => $restaurant->Address,
            ':cuisine_type' => $restaurant->CuisineType->value,
            ':stars' => $restaurant->Stars,
            ':base_price' => $restaurant->BasePrice,
            ':reduced_price' => $restaurant->ReducedPrice,
            ':total_seats' => $restaurant->TotalSeats,
            ':image_path' => $restaurant->ImagePath,
            ':id' => $restaurant->Id,
        ]);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM restaurants WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
