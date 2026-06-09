<?php
namespace App\Controllers;

use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\ITicketScanService;
use App\Services\TicketScanService;

class ScannerController
{
    private ITicketScanService $scanService;

    public function __construct()
    {
        $this->scanService = new TicketScanService();
    }

    public function index(): void
    {
        AuthMiddleware::requireStaff();
        View::render('Scanner/index', [
            'result' => null,
            'code' => '',
        ], 'Ticket scanner');
    }

    public function scan(): void
    {
        AuthMiddleware::requireStaff();

        $code = trim($_POST['code'] ?? '');
        View::render('Scanner/index', [
            'result' => $this->scanService->scan($code),
            'code' => $code,
        ], 'Ticket scanner');
    }
}
