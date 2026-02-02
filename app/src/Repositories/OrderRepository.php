<?php
namespace App\Repositories;
use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;
use PDO;

class OrderRepository extends Repository implements IOrderRepository
{

    public function saveOrder(int $userId, string $address, string $paymentMethod, int $total) :int
    {
        $sql = "INSERT INTO orders (UserId, Address, PaymentMethod, TotalAmount)
                VALUES (:userId, :address, :paymentMethod, :total)";

        $db = $this->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':userId'           => $userId,
            ':address'          => $address,
            ':paymentMethod'    => $paymentMethod,
            ':total'            => $total
        ]);
        return (int)$db->lastInsertId();
    }

    public function saveOrderItem(int $orderId, int $productId, int $quantity, int $price) :void
    {
        $db = $this->getConnection();
        $sql = "INSERT INTO orderItems (OrderId, ProductId, Quantity, PriceAtPurchase)
                VALUES (:orderId, :productId, :quantity, :price)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':orderId'      => $orderId,
            ':productId'    => $productId,
            ':quantity'     => $quantity,
            ':price'        => $price
        ]);
    }

    public function getOrderDetails(int $orderId): ?array
    {
        $sql = "SELECT o.*, u.FirstName, u.LastName, u.Email 
                FROM orders o
                JOIN users u ON o.UserId = u.UserId
                WHERE o.OrderId = :orderId";
                
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':orderId' => $orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) return null;
        
        $sqlItems = "SELECT oi.Quantity, oi.PriceAtPurchase, p.ProductName 
                    FROM orderItems oi
                    JOIN products p ON oi.ProductId = p.ProductId
                    WHERE oi.OrderId = :orderId";
                    
        $stmtItems = $this->getConnection()->prepare($sqlItems);
        $stmtItems->execute([':orderId' => $orderId]);
        $order['items'] = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

        return $order;
    }
}