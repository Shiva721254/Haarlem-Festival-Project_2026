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
    public function createFromCart(int $userId, string $sessionId): array;

    public function getById(int $id): ?OrderModel;

    /** @return OrderModel[] */
    public function getByUser(int $userId): array;

    /** @return OrderModel[] */
    public function getPendingPayLaterForUser(int $userId): array;

    public function cancelMessage(?OrderModel $order): string;

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

    /** @return string[] */
    public function adminStatuses(): array;

    /** @return array<string,string> */
    public function exportColumns(): array;

    public function normalizeAdminStatus(?string $status): ?string;

    /**
     * @param string[] $selected
     * @return string[]
     */
    public function resolveExportColumns(array $selected): array;

    /**
     * @param string[] $columns
     * @return array{filename:string,contentType:string,body:string}
     */
    public function buildExport(?string $status, array $columns, string $format): array;

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void;

    /**
     * Finalise a paid order: stamp it paid, issue tickets, reduce stock, clear cart.
     */
    public function fulfill(OrderModel $order, ?string $sessionId = null): void;

    /** @param array{paid:bool,payment_intent:?string,order_id:?int} $info */
    public function fulfillPaidCheckout(array $info): void;
}
