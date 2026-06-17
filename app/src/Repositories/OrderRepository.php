<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use PDO;

class OrderRepository extends Repository implements IOrderRepository
{
    private const ISSUED_TICKETS_SQL =
        'SELECT t.qr_code, t.status,
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

    private const ITEMS_SQL =
        'SELECT oi.*, tt.name AS ticket_type_name, e.title AS event_title
         FROM order_items oi
         JOIN ticket_types tt ON tt.id = oi.ticket_type_id
         JOIN events e ON e.id = tt.event_id
         WHERE oi.order_id = :oid';

    public function create(OrderModel $order): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();
        try {
            $orderId = $this->insertOrder($pdo, $order);
            $this->insertItems($pdo, $orderId, $order->items);
            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function insertOrder(PDO $pdo, OrderModel $order): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO orders (user_id, status, subtotal, vat_total, total, pay_later_until)
             VALUES (:user_id, :status, :subtotal, :vat_total, :total, :pay_later_until)'
        );
        $stmt->execute($this->orderParams($order));
        return (int)$pdo->lastInsertId();
    }

    private function insertItems(PDO $pdo, int $orderId, array $items): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price, vat_rate, special_requests)
             VALUES (:order_id, :ticket_type_id, :quantity, :unit_price, :vat_rate, :special_requests)'
        );
        foreach ($items as $item) {
            $stmt->execute($this->itemParams($orderId, $item));
        }
    }

    /** @return array<string,mixed> */
    private function orderParams(OrderModel $order): array
    {
        return [
            'user_id'         => $order->user_id,
            'status'          => $order->status,
            'subtotal'        => $order->subtotal,
            'vat_total'       => $order->vat_total,
            'total'           => $order->total,
            'pay_later_until' => $order->pay_later_until,
        ];
    }

    /** @return array<string,mixed> */
    private function itemParams(int $orderId, OrderItemModel $item): array
    {
        return [
            'order_id'         => $orderId,
            'ticket_type_id'   => $item->ticket_type_id,
            'quantity'         => $item->quantity,
            'unit_price'       => $item->unit_price,
            'vat_rate'         => $item->vat_rate,
            'special_requests' => $item->special_requests,
        ];
    }

    public function getById(int $id): ?OrderModel
    {
        return $this->loadOrder('SELECT * FROM orders WHERE id = :id', ['id' => $id], $id);
    }

    public function getByUser(int $userId): array
    {
        $rows = $this->fetchAll('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC', ['uid' => $userId]);
        return array_map(static fn(array $r) => OrderModel::fromDb($r), $rows);
    }

    public function getByIdForUser(int $orderId, int $userId): ?OrderModel
    {
        $sql = 'SELECT * FROM orders WHERE id = :id AND user_id = :uid';
        return $this->loadOrder($sql, ['id' => $orderId, 'uid' => $userId], $orderId);
    }

    /** Fetch a single order by query and attach its items, or null. */
    private function loadOrder(string $sql, array $params, int $orderId): ?OrderModel
    {
        $row = $this->fetchOne($sql, $params);
        if ($row === null) {
            return null;
        }
        $order = OrderModel::fromDb($row);
        $order->items = $this->loadItems($orderId);
        return $order;
    }

    public function getAllForAdmin(?string $status = null): array
    {
        [$where, $params] = $this->statusFilter($status);
        $cols = 'o.*, CONCAT(u.FirstName, " ", u.LastName) AS customer_name,
                 u.Email AS customer_email, COALESCE(SUM(oi.quantity), 0) AS item_count';
        $rows = $this->fetchAll($this->ordersReportSql($cols, $where), $params);
        return array_map(static fn(array $row) => OrderModel::fromDb($row), $rows);
    }

    public function getExportRows(?string $status = null): array
    {
        [$where, $params] = $this->statusFilter($status);
        $cols = 'o.id, o.invoice_number, o.status, o.subtotal, o.vat_total, o.total, o.created_at,
                 o.paid_at, o.payment_intent_id, CONCAT(u.FirstName, " ", u.LastName) AS customer_name,
                 u.Email AS customer_email, COALESCE(SUM(oi.quantity), 0) AS item_count';
        return $this->fetchAll($this->ordersReportSql($cols, $where), $params);
    }

    /** Shared orders-with-customer report query, varying only columns and filter. */
    private function ordersReportSql(string $selectCols, string $where): string
    {
        return "SELECT {$selectCols}
                FROM orders o
                JOIN users u ON u.UserId = o.user_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                {$where}
                GROUP BY o.id, u.FirstName, u.LastName, u.Email
                ORDER BY o.created_at DESC";
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

    public function getItemQuantities(int $orderId): array
    {
        $items = $this->fetchAll('SELECT id, quantity FROM order_items WHERE order_id = :oid', ['oid' => $orderId]);
        $quantities = [];
        foreach ($items as $item) {
            $quantities[(int)$item['id']] = (int)$item['quantity'];
        }
        return $quantities;
    }

    public function issueTickets(array $codesByItemId): void
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO tickets (order_item_id, qr_code, status) VALUES (:oi, :qr, "valid")'
        );
        foreach ($codesByItemId as $orderItemId => $codes) {
            $this->issueTicketsForItem($stmt, (int)$orderItemId, $codes);
        }
    }

    private function issueTicketsForItem(\PDOStatement $stmt, int $orderItemId, array $codes): void
    {
        foreach ($codes as $code) {
            $stmt->execute(['oi' => $orderItemId, 'qr' => $code]);
        }
    }

    /**
     * Issued tickets for an order with the detail needed on a PDF ticket.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getIssuedTickets(int $orderId): array
    {
        return $this->fetchAll(self::ISSUED_TICKETS_SQL, ['oid' => $orderId]);
    }

    /** @return OrderItemModel[] */
    private function loadItems(int $orderId): array
    {
        $rows = $this->fetchAll(self::ITEMS_SQL, ['oid' => $orderId]);
        return array_map(static fn(array $r) => OrderItemModel::fromDb($r), $rows);
    }

    /** @return array{0:string,1:array<string,string>} */
    private function statusFilter(?string $status): array
    {
        $allowed = ['pending', 'paid', 'failed', 'cancelled'];
        if ($status === null || $status === '' || !in_array($status, $allowed, true)) {
            return ['', []];
        }
        return ['WHERE o.status = :status', ['status' => $status]];
    }
}
