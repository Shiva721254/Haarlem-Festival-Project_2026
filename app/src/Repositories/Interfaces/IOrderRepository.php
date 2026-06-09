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

    /**
     * Issue one ticket (with a unique QR code) per unit across the order's items.
     */
    public function issueTickets(int $orderId): void;

    /**
     * Issued tickets for an order, joined with event/venue detail for the PDF.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getIssuedTickets(int $orderId): array;

    /**
     * A user's personal program — events they hold paid tickets for.
     *
     * @return \App\Models\ProgramItemModel[]
     */
    public function getProgramEvents(int $userId): array;
}
