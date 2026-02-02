<?php 
namespace App\Repositories\Interfaces;
interface IOrderRepository
{
    public function saveOrder(int $userId, string $address, string $paymentMethod, int $total) :int;
    public function saveOrderItem(int $orderId, int $productId, int $quantity, int $price) :void;
    public function getOrderDetails(int $orderId): ?array;
}