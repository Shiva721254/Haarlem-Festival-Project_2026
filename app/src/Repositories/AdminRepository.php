<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IAdminRepository;

class AdminRepository extends Repository implements IAdminRepository
{
    public function dashboardCounts(): array
    {
        return [
            'eventCount' => $this->countRows('events'),
            'ticketTypeCount' => $this->countRows('ticket_types'),
            'orderCount' => $this->countRows('orders'),
            'venueCount' => $this->countRows('venues'),
            'restaurantCount' => $this->countRows('restaurants'),
            'artistCount' => $this->countRows('artists'),
            'homepageCount' => $this->countRows('content_blocks', 'page_slug = :page', ['page' => 'home']),
            'userCount' => $this->countRows('users'),
        ];
    }

    /** @param array<string,mixed> $params */
    private function countRows(string $table, string $where = '', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) AS n FROM ' . $table;
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $row = $this->fetchOne($sql, $params);
        return (int)($row['n'] ?? 0);
    }
}
