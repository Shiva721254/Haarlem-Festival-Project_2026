<?php

namespace App\Controllers;

use App\Services\Interfaces\IProgramService;
use App\Framework\View;
use App\Middleware\AuthMiddleware;

/**
 * A logged-in customer's personal program — the events they bought tickets for.
 */
class ProgramController
{
    private IProgramService $programService;

    public function __construct(IProgramService $programService)
    {
        $this->programService = $programService;
    }

    // GET: /program
    public function index(): void
    {
        $items = $this->programService->getForUser(AuthMiddleware::userId());
        View::render('Program/index', ['items' => $items], 'My program');
    }
}
