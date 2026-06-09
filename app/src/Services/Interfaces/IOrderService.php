<?php
namespace App\Services\Interfaces;

use App\Models\OrderModel;

interface IOrderService
{
    /**
     * Build a pending order from the current user's cart.
     *
     * @return array{ok:bool,order:?OrderModel,message:string}
     */
    public function createFromCart(int $userId): array;

    public function getById(int $id): ?OrderModel;

    /** @return OrderModel[] */
    public function getByUser(int $userId): array;

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void;

    /**
     * Finalise a paid order: stamp it paid, issue tickets, reduce stock, clear cart.
     */
    public function fulfill(OrderModel $order): void;
}
