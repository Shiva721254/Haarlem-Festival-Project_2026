<?php
namespace App\Services\Interfaces;

interface IOrderService
{
    public function checkout(int $userId, string $address, string $paymentMethod): int;
    public function sendOrderConfirmationEmail(int $orderId);
}