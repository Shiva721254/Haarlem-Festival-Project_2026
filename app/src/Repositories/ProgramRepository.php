<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ProgramItemModel;
use App\Repositories\Interfaces\IProgramRepository;
use App\Enums\OrderStatus;

class ProgramRepository extends Repository implements IProgramRepository
{
    public function getForUser(int $userId): array
    {
        return array_map(
            static fn(array $row) => ProgramItemModel::fromDb($row),
            $this->fetchAll($this->programSql(), ['uid' => $userId, 'status' => OrderStatus::Paid->value])
        );
    }

    private function programSql(): string
    {
        return 'SELECT e.id AS event_id, e.title, e.starts_at, e.ends_at, e.image, v.name AS venue_name,
                       et.slug AS type_slug, et.name AS type_name,
                       GROUP_CONCAT(DISTINCT tt.name ORDER BY tt.name SEPARATOR ", ") AS ticket_types,
                       SUM(oi.quantity) AS total_tickets FROM orders o
                JOIN order_items oi ON oi.order_id = o.id JOIN ticket_types tt ON tt.id = oi.ticket_type_id
                JOIN events e ON e.id = tt.event_id JOIN event_types et ON et.id = e.event_type_id
                LEFT JOIN venues v ON v.id = e.venue_id WHERE o.user_id = :uid AND o.status = :status
                GROUP BY e.id, e.title, e.starts_at, e.ends_at, e.image, v.name, et.slug, et.name ORDER BY e.starts_at';
    }
}
