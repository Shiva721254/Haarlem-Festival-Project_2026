<?php
namespace App\Models;
use App\Enums\Address;
use App\Enums\UserRole;
 
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
            ':total'            => $tot