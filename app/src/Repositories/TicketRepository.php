<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\ITicketRepository;

class TicketRepository extends Repository implements ITicketRepository
{
    public function findScanInfoByCode(string $code): ?array
    {
        $sql = 'SELECT t.id, t.qr_code, t.status, t.scanned_at,
                       tt.name AS ticket_type_name,
                       e.title AS event_title,
                       e.starts_at,
                       v.name AS venue_name,
                       o.id AS order_id,
                       o.status AS order_status,
                       CONCAT(u.FirstName, " ", u.LastName) AS customer_name,
                       u.Email AS customer_email
                FROM tickets t
                JOIN order_items oi ON oi.id = t.order_item_id
                JOIN orders o ON o.id = oi.order_id
                JOIN users u ON u.UserId = o.user_id
                JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                JOIN events e ON e.id = tt.event_id
                LEFT JOIN venues v ON v.id = e.venue_id
                WHERE t.qr_code = :code';

        return $this->fetchOne($sql, ['code' => $code]);
    }

    public function markScanned(int $ticketId): void
    {
        $this->execute(
            'UPDATE tickets SET status = "scanned", scanned_at = NOW()
             WHERE id = :id AND status = "valid"',
            ['id' => $ticketId]
        );
    }
}
