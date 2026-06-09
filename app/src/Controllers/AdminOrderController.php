<?php
namespace App\Controllers;

use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IOrderService;
use App\Services\OrderService;

class AdminOrderController
{
    private const STATUSES = ['pending', 'paid', 'failed', 'cancelled'];

    private IOrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        $status = $this->statusFilter();
        View::renderAdmin('Admin/orders/index', [
            'orders' => $this->orderService->getAllForAdmin($status),
            'status' => $status,
            'statuses' => self::STATUSES,
        ], 'Orders');
    }

    private function statusFilter(): ?string
    {
        $status = $_GET['status'] ?? null;
        return in_array($status, self::STATUSES, true) ? $status : null;
    }
}
