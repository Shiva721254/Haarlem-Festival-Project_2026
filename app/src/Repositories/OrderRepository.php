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

    public function getAllForAdmin(?string $status = null): array
    {
        [$where, $params] = $this->statusFilter($status);
        $sql = 'SELECT o.*,
                       CONCAT(u.FirstName, " ", u.LastName) AS customer_name,
                       u.Email AS customer_email,
                       COALESCE(SUM(oi.quantity), 0) AS item_count
                FROM orders o
                JOIN users u ON u.UserId = o.user_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                ' . $where . '
                GROUP BY o.id, u.FirstName, u.LastName, u.Email
                ORDER BY o.created_at DESC';

        return array_map(static fn(array $row) => OrderModel::fromDb($row), $this->fetchAll($sql, $params));
    }

    public function getExportRows(?string $status = null): array
    {
        [$where, $params] = $this->statusFilter($status);
        $sql = 'SELECT o.id,
                       o.invoice_number,
                       o.status,
                       o.subtotal,
                       o.vat_total,
                       o.total,
                       o.created_at,
                       o.paid_at,
                       o.payment_intent_id,
                       CONCAT(u.FirstName, " ", u.LastName) AS customer_name,
                       u.Email AS customer_email,
                       COALESCE(SUM(oi.quantity), 0) AS item_count
                FROM orders o
                JOIN users u ON u.UserId = o.user_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                ' . $where . '
                GROUP BY o.id, u.FirstName, u.LastName, u.Email
                ORDER BY o.created_at DESC';

        return $this->fetchAll($sql, $params);
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
     * A user's personal program: the events they hold paid tickets for,
     * one row per event, chronologically.
     *
     * @return \App\Models\ProgramItemModel[]
     */
    public function getProgramEvents(int $userId): array
    {
        $sql = 'SELECT e.id AS event_id, e.title, e.starts_at, e.ends_at, e.image,
                       v.name AS venue_name,
                       et.slug AS type_slug, et.name AS type_name,
                       GROUP_CONCAT(DISTINCT tt.name ORDER BY tt.name SEPARATOR ", ") AS ticket_types,
                       SUM(oi.quantity) AS total_tickets
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.id
                JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                JOIN events e ON e.id = tt.event_id
                JOIN event_types et ON et.id = e.event_type_id
                LEFT JOIN venues v ON v.id = e.venue_id
                WHERE o.user_id = :uid AND o.status = "paid"
                GROUP BY e.id, e.title, e.starts_at, e.ends_at, e.image, v.name, et.slug, et.name
                ORDER BY e.starts_at';

        return array_map(
            static fn(array $r) => \App\Models\ProgramItemModel::fromDb($r),
            $this->fetchAll($sql, ['uid' => $userId])
        );
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

    /**
     * @return array{0:string,1:array<string,string>}
     */
    private function statusFilter(?string $status): array
    {
        $allowed = ['pending', 'paid', 'failed', 'cancelled'];
        if ($status === null || $status === '' || !in_array($status, $allowed, true)) {
            return ['', []];
        }
        return ['WHERE o.status = :status', ['status' => $status]];
    }
}
