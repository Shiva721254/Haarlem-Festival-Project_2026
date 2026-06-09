<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use PDO;

class OrderRepository extends Repository implements IOrderRepository
{
    public function create(OrderModel $order): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, status, subtotal, vat_total, total, pay_later_until)
                 VALUES (:user_id, :status, :subtotal, :vat_total, :total, :pay_later_until)'
            );
            $stmt->execute([
                'user_id'         => $order->user_id,
                'status'          => $order->status,
                'subtotal'        => $order->subtotal,
                'vat_total'       => $order->vat_total,
                'total'           => $order->total,
                'pay_later_until' => $order->pay_later_until,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price, vat_rate)
                 VALUES (:order_id, :ticket_type_id, :quantity, :unit_price, :vat_rate)'
            );
            foreach ($order->items as $item) {
                $itemStmt->execute([
                    'order_id'       => $orderId,
                    'ticket_type_id' => $item->ticket_type_id,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'vat_rate'       => $item->vat_rate,
                ]);
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): ?OrderModel
    {
        $row = $this->fetchOne('SELECT * FROM orders WHERE id = :id', ['id' => $id]);
        if ($row === null) {
            return null;
        }
        $order = OrderModel::fromDb($row);
        $order->items = $this->loadItems($id);
        return $order;
    }

    public function getByUser(int $userId): array
    {
        $rows = $this->fetchAll('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC', ['uid' => $userId]);
        return array_map(static fn(array $r) => OrderModel::fromDb($r), $rows);
    }

    public function setPaymentIntent(int $orderId, string $paymentIntentId): void
    {
        $this->execute(
            'UPDATE orders SET payment_intent_id = :pi WHERE id = :id',
            ['pi' => $paymentIntentId, 'id' => $orderId]
        );
    }

    public function markPaid(int $orderId, string $invoiceNumber): void
    {
        $this->execute(
            'UPDATE orders SET status = "paid", invoice_number = :inv, paid_at = NOW() WHERE id = :id',
            ['inv' => $invoiceNumber, 'id' => $orderId]
        );
    }

    public function issueTickets(int $orderId): void
    {
        $items = $this->fetchAll('SELECT id, quantity FROM order_items WHERE order_id = :oid', ['oid' => $orderId]);
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO tickets (order_item_id, qr_code, status) VALUES (:oi, :qr, "valid")'
        );
        foreach ($items as $item) {
            for ($n = 0; $n < (int)$item['quantity']; $n++) {
                // Random, non-guessable code (anti-fraud), not a sequential id.
                $stmt->execute(['oi' => (int)$item['id'], 'qr' => bin2hex(random_bytes(16))]);
            }
        }
    }

    /**
     * Issued tickets for an order with the detail needed on a PDF ticket.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getIssuedTickets(int $orderId): array
    {
        $sql = 'SELECT t.qr_code, t.status,
                       tt.name AS ticket_type_name,
                       e.title AS event_title, e.starts_at,
                       v.name AS venue_name
                FROM tickets t
                JOIN order_items oi ON oi.id = t.order_item_id
                JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                JOIN events e ON e.id = tt.event_id
                LEFT JOIN venues v ON v.id = e.venue_id
                WHERE oi.order_id = :oid
                ORDER BY e.starts_at';
        return $this->fetchAll($sql, ['oid' => $orderId]);
    }

    /**
     * @return OrderItemModel[]
     */
    private function loadItems(int $orderId): array
    {
        $sql = 'SELECT oi.*, tt.name AS ticket_type_name, e.title AS event_title
                FROM order_items oi
                JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                JOIN events e ON e.id = tt.event_id
                WHERE oi.order_id = :oid';
        return array_map(
            static fn(array $r) => OrderItemModel::fromDb($r),
            $this->fetchAll($sql, ['oid' => $orderId])
        );
    }
}
