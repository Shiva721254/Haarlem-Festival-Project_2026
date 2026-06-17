<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\Interfaces\IContentService;
use App\Services\Interfaces\IEventService;
use App\Services\Interfaces\IVenueService;

class HomeController
{
    private IContentService $contentService;
    private IEventService $eventService;
    private IVenueService $venueService;

    public function __construct(IContentService $contentService, IEventService $eventService, IVenueService $venueService)
    {
        $this->contentService = $contentService;
        $this->eventService = $eventService;
        $this->venueService = $venueService;
    }

    public function index(): void
    {
        View::render('Home/index', [
            'blocks'    => $this->contentService->getPageBlocks('home'),
            'summaries' => $this->eventService->getHomeSummaries(),
            'passes'    => $this->eventService->getGroupedPassSummaries(),
            'schedule'  => $this->eventService->getGroupedScheduleSummary(),
            'locations' => $this->venueService->getFestivalLocations(),
        ], 'Haarlem Festival');
    }

    // GET: /privacy
    public function privacy(): void
    {
        View::render('Home/privacy', [], 'Privacy policy');
    }
}
