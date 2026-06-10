<?php

namespace App\Controllers;

use App\Framework\Repository;
use App\Services\EventService;
use App\Services\UserService;
use App\Services\OrderService;
use App\Services\VenueService;
use App\Services\RestaurantService;
use App\Services\ArtistService;
use App\Framework\View;
use App\Middleware\AuthMiddleware;

/**
 * Admin dashboard landing page.
 */
class AdminController
{
    // GET: /admin
    public function dashboard(): void
    {
        AuthMiddleware::requireAdmin();

        $eventService = new EventService();
        $userService = new UserService();
        $orderService = new OrderService();
        $venueService = new VenueService();
        $restaurantService = new RestaurantService();
        $artistService = new ArtistService();

        View::renderAdmin('Admin/dashboard', [
            'eventCount'      => count($eventService->getAllForAdmin()),
            'ticketTypeCount' => $this->countRows('ticket_types'),
            'orderCount'      => count($orderService->getAllForAdmin()),
            'venueCount'      => count($venueService->getAll()),
            'restaurantCount' => count($restaurantService->getAll()),
            'artistCount'     => count($artistService->getAll()),
            'homepageCount'   => $this->countRows('content_blocks', 'page_slug = :page', ['page' => 'home']),
            'userCount'       => count($userService->getAll()),
        ], 'Dashboard');
    }

    /**
     * Count rows for small dashboard statistics. Table names are controlled by
     * this controller, not user input.
     *
     * @param array<string,mixed> $params
     */
    private function countRows(string $table, string $where = '', array $params = []): int
    {
        $repo = new Repository();
        $sql = 'SELECT COUNT(*) FROM ' . $table;
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $repo->getConnection()->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }
}
