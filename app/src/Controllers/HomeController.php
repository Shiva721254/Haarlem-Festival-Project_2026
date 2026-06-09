<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\ContentService;
use App\Services\EventService;
use App\Services\Interfaces\IEventService;

class HomeController
{
    private ContentService $contentService;
    private IEventService $eventService;

    public function __construct()
    {
        $this->contentService = new ContentService();
        $this->eventService = new EventService();
    }

    public function index(): void
    {
        // Group the flat pass list by event type for the passes section.
        $passes = [];
        foreach ($this->eventService->getPassSummaries() as $p) {
            $passes[$p['type_name']][] = $p;
        }

        View::render('Home/index', [
            'blocks'    => $this->contentService->getPageBlocks('home'),
            'summaries' => $this->eventService->getHomeSummaries(),
            'passes'    => $passes,
        ], 'Haarlem Festival');
    }

    // GET: /privacy
    public function privacy(): void
    {
        View::render('Home/privacy', [], 'Privacy policy');
    }
}
