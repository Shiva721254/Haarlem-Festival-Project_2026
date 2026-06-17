<?php
namespace App\Repositories\Interfaces;

use App\Models\OrderModel;

interface IOrderRepository
{
    /**
     * Persist an order and its items (transactional). Returns the new order id.
     */
    public function create(OrderModel $order): int;

    public function getById(int $id): ?OrderModel;

    /** @return OrderModel[] */
    public function getByUser(int $userId): array;

    public function getByIdForUser(int $orderId, int $userId): ?OrderModel;

    /** @return OrderModel[] */
    public function getAllForAdmin(?string $status = null): array;

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getExportRows(?string $status = null): array;

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void;

    /**
     * Mark an order paid and stamp the invoice number + paid timestamp.
     */
    public function markPaid(int $orderId, string $invoiceNumber): void;

    /** @return array<int,int> order_item_id => quantity */
    public function getItemQuantities(int $orderId): array;

    /** @param array<int,string[]> $codesByItemId */
    public function issueTickets(array $codesByItemId): void;

    /**
     * Issued tickets for an order, joined with event/venue detail for the PDF.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getIssuedTickets(int $orderId): array;

}
