<?php
namespace App\Repositories;
use App\Framework\Repository;
use App\Models\ShoppingCartModel;
use App\Repositories\Interfaces\IShoppingCartRepository;
use \PDO;

class ShoppingCartRepository extends Repository implements IShoppingCartRepository
{
    
    public function addProductToShoppingCart(int $userId, int $productId, int $quantity = 1): void
    {
        $sql = 'INSERT INTO shoppingCart (UserId, ProductId, Quantity) 
                VALUES (:UserId, :ProductId, :Quantity)
                ON DUPLICATE KEY UPDATE Quantity = Quantity + :QuantityUpdate';

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':UserId' => $userId,
                ':ProductId' => $productId,
                ':Quantity' => $quantity,
                ':QuantityUpdate' => $quantity
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Could not add product to cart: " . $e->getMessage());
        }
    }

    public function getShoppingCart(int $userId): array
    {
        $sql = 'SELECT p.ProductId, p.ProductName, p.Price, c.Quantity, (p.Price * c.Quantity) AS subtotal
                FROM shoppingCart c
                JOIN products p ON c.ProductId = p.ProductId
                WHERE c.UserId = :userId';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grandTotal = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $grandTotal += (float)$item['subtotal'];
            $totalQuantity += (int)$item['Quantity'];
        }

        return [
            'items'      => $items,
            'grandTotal' => $grandTotal,
            'itemCount'  => $totalQuantity
        ];
    }

    public function emptyCart($userId): void
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM shoppingCart WHERE UserId = :UserId");
        $stmt->bindValue(':UserId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}