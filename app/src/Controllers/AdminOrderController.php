<?php
namespace App\Controllers;

use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IOrderService;
use App\Services\OrderService;

class AdminOrderController
{
    private const STATUSES = ['pending', 'paid', 'failed', 'cancelled'];
    private const EXPORT_COLUMNS = [
        'id' => 'Order ID',
        'invoice_number' => 'Invoice number',
        'status' => 'Status',
        'customer_name' => 'Customer name',
        'customer_email' => 'Customer email',
        'item_count' => 'Items',
        'subtotal' => 'Subtotal',
        'vat_total' => 'VAT',
        'total' => 'Total',
        'created_at' => 'Created at',
        'paid_at' => 'Paid at',
        'payment_intent_id' => 'Payment reference',
    ];

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
            'exportColumns' => self::EXPORT_COLUMNS,
        ], 'Orders');
    }

    public function export(): void
    {
        AuthMiddleware::requireAdmin();

        $status = $this->statusFilter();
        $columns = $this->selectedColumns();
        $rows = $this->orderService->getExportRows($status);

        $filename = 'orders-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, array_map(static fn(string $key) => self::EXPORT_COLUMNS[$key], $columns), ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map(static fn(string $key) => $row[$key] ?? '', $columns), ',', '"', '');
        }
        fclose($out);
        exit();
    }

    private function statusFilter(): ?string
    {
        $status = $_GET['status'] ?? null;
        return in_array($status, self::STATUSES, true) ? $status : null;
    }

    /** @return string[] */
    private function selectedColumns(): array
    {
        $selected = $_GET['columns'] ?? [];
        if (!is_array($selected)) {
            return array_keys(self::EXPORT_COLUMNS);
        }

        $columns = array_values(array_filter($selected, static fn($key) => isset(self::EXPORT_COLUMNS[$key])));
        return empty($columns) ? array_keys(self::EXPORT_COLUMNS) : $columns;
    }
}
