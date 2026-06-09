<?php

namespace App\Controllers;

use App\Services\ProgramService;
use App\Services\Interfaces\IProgramService;
use App\Framework\View;
use App\Middleware\AuthMiddleware;

/**
 * A logged-in customer's personal program — the events they bought tickets for.
 */
class ProgramController
{
    private IProgramService $programService;

    public function __construct()
    {
        $this->programService = new ProgramService();
    }

    // GET: /program
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $items = $this->programService->getForUser((int) $_SESSION['UserId']);
        View::render('Program/index', ['items' => $items], 'My program');
    }
}
