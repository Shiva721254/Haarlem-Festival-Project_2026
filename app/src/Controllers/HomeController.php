<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\ContentService;

class HomeController
{
    private ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    public function index(): void
    {
        View::render('Home/index', [
            'blocks' => $this->contentService->getPageBlocks('home'),
        ], 'Haarlem Festival');
    }
}
