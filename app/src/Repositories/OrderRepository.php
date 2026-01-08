<?php
namespace App\Repositories;
use App\Framework\Repository;
use App\Repositories\IOrderRepository;
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
}