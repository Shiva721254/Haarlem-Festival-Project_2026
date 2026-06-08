<?php

namespace App\Controllers;

use App\Services\EventService;
use App\Services\UserService;
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

        View::renderAdmin('Admin/dashboard', [
            'eventCount' => count($eventService->getAllForAdmin()),
            'userCount'  => count($userService->getAll()),
        ], 'Dashboard');
    }
}
