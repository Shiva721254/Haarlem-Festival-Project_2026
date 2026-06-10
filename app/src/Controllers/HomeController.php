<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\ContentService;
use App\Services\EventService;
use App\Services\VenueService;
use App\Services\Interfaces\IEventService;
use App\Services\Interfaces\IVenueService;

class HomeController
{
    private ContentService $contentService;
    private IEventService $eventService;
    private IVenueService $venueService;

    public function __construct()
    {
        $this->contentService = new ContentService();
        $this->eventService = new EventService();
        $this->venueService = new VenueService();
    }

    public function index(): void
    {
        // Group the flat pass list by event type for the passes section.
        $passes = [];
        foreach ($this->eventService->getPassSummaries() as $p) {
            $passes[$p['type_name']][] = $p;
        }

        // Group the schedule by festival day for the condensed schedule strip.
        $schedule = [];
        foreach ($this->eventService->getScheduleSummary() as $row) {
            $schedule[$row['day']][] = $row;
        }

        View::render('Home/index', [
            'blocks'    => $this->contentService->getPageBlocks('home'),
            'summaries' => $this->eventService->getHomeSummaries(),
            'passes'    => $passes,
            'schedule'  => $schedule,
            'locations' => $this->venueService->getFestivalLocations(),
        ], 'Haarlem Festival');
    }

    // GET: /privacy
    public function privacy(): void
    {
        View::render('Home/privacy', [], 'Privacy policy');
    }
}
