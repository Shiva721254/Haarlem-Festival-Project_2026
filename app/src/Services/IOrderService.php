<?php
namespace App\Services;

interface IOrderService
{
    public function checkout(int $userId, string $address, string $paymentMethod): int;
}