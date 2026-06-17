<?php
namespace App\Controllers;

use App\Framework\Http;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IOrderService;
use App\Services\Interfaces\IUserService;

class AdminOrderController
{
    private IOrderService $orderService;
    private IUserService $userService;

    public function __construct(IOrderService $orderService, IUserService $userService)
    {
        $this->orderService = $orderService;
        $this->userService = $userService;
    }

    public function show(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $order = $this->orderService->getById((int)($vars['id'] ?? 0));
        if ($order === null) {
            Http::notFound('Order not found');
        }
        View::renderAdmin('Admin/orders/show', [
            'order' => $order,
            'customer' => $this->userService->getById($order->user_id),
        ], 'Order #' . $order->id);
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        $status = $this->orderService->normalizeAdminStatus($_GET['status'] ?? null);
        View::renderAdmin('Admin/orders/index', [
            'orders' => $this->orderService->getAllForAdmin($status),
            'status' => $status,
            'statuses' => $this->orderService->adminStatuses(),
            'exportColumns' => $this->orderService->exportColumns(),
        ], 'Orders');
    }

    public function export(): void
    {
        AuthMiddleware::requireAdmin();
        $status = $this->orderService->normalizeAdminStatus($_GET['status'] ?? null);
        $columns = $this->orderService->resolveExportColumns(is_array($_GET['columns'] ?? []) ? $_GET['columns'] : []);
        $format = ($_GET['format'] ?? 'csv') === 'xlsx' ? 'xlsx' : 'csv';
        $export = $this->orderService->buildExport($status, $columns, $format);
        $this->sendDownload($export);
    }

    /** Stream a generated export (CSV/XLSX) as a file download. */
    private function sendDownload(array $export): never
    {
        header('Content-Type: ' . $export['contentType']);
        header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
        header('Content-Length: ' . strlen($export['body']));
        echo $export['body'];
        exit();
    }
}
