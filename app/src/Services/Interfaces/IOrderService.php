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

    public function getByIdForUser(int $orderId, int $userId): ?OrderModel;

    /**
     * @return array{ok:bool,message:string}
     */
    public function canStartPayment(OrderModel $order): array;

    /** @return OrderModel[] */
    public function getAllForAdmin(?string $status = null): array;

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getExportRows(?string $status = null): array;

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void;

    /**
     * Finalise a paid order: stamp it paid, issue tickets, reduce stock, clear cart.
     */
    public function fulfill(OrderModel $order): void;
}
