<?php 
namespace App\Repositories;
use App\Repositories\Interfaces\IProductRatingRepository;
use App\Models\ProductRating;
use App\Framework\Repository;

class ProductRatingRepository extends Repository implements IProductRatingRepository 
{
    public function bindParams($stmt, ProductRating $rating)
    {
        $stmt->bindValue(':ProductId', $rating->ProductId, \PDO::PARAM_INT);
        $stmt->bindValue(':UserId', $rating->UserId, \PDO::PARAM_INT);
        $stmt->bindValue(':Rating', $rating->Rating, $rating->Rating !== null ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        $stmt->bindValue(':Review', $rating->Review, $rating->Review !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }

    public function addRating(ProductRating $rating): bool
    {
        $sql = "INSERT INTO productRating (ProductId, UserId, Rating, Review) 
                VALUES (:ProductId, :UserId, :Rating, :Review)";

        $this->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $this->getConnection()->prepare($sql);

        $this->bindParams($stmt, $rating);

        // $stmt->debugDumpParams(); die();

        $stmt->execute();
        return true;
    }

    public function updateRating(ProductRating $rating): bool
    {
        $sql = "UPDATE productRating 
                SET Rating = :Rating, Review = :Review 
                WHERE ProductId = :ProductId AND UserId = :UserId";
        $stmt = $this->getConnection()->prepare($sql);
        $this->bindParams($stmt, $rating);
        $stmt->execute();
        return true;
    }

    public function deleteRating(int $productId, int $userId): bool
    {
        $sql = "DELETE FROM productRating WHERE ProductId = :ProductId AND UserId = :UserId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $productId, \PDO::PARAM_INT);
        $stmt->bindValue(':UserId', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public function getRatingByProductId(int $productId): ?ProductRating
    {
        $sql = "SELECT r.*, u.FirstName, u.LastName 
                FROM productRating r
                JOIN users u ON r.UserId = u.UserId
                WHERE r.ProductId = :ProductId
                ORDER BY r.CreatedAt DESC";
                
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? ProductRating::fromDb($data) : null;
    }

    public function getAllRatingsByProductId(int $productId): array
    {
        $sql = "SELECT r.*, u.FirstName, u.LastName 
                FROM productRating r
                JOIN users u ON r.UserId = u.UserId
                WHERE r.ProductId = :ProductId
                ORDER BY r.CreatedAt DESC";
                
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        
        // Use fetchAll to get every review
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Map the array of data into an array of ProductRating objects
        return array_map(fn($data) => ProductRating::fromDb($data), $rows);
    }

    public function getNumberOfRatingsByProductId(int $productId): int
    {
        $sql = "SELECT COUNT(*) as count FROM productRating WHERE ProductId = :ProductId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getAverageRatingByProductId(int $productId): float
    {
        $sql = "SELECT AVG(Rating) as average FROM productRating WHERE ProductId = :ProductId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':ProductId', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }
}