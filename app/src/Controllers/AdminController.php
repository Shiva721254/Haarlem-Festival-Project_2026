<?php

namespace App\Controllers;

use App\Services\Interfaces\IAdminService;
use App\Framework\View;
use App\Middleware\AuthMiddleware;

/**
 * Admin dashboard landing page.
 */
class AdminController
{
    private IAdminService $adminService;

    public function __construct(IAdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    // GET: /admin
    public function dashboard(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/dashboard', $this->adminService->getDashboardStats(), 'Dashboard');
    }
}
