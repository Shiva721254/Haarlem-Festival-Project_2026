<?php
namespace App\Controllers;

use App\Services\Interfaces\IEventService;
use App\Services\Interfaces\ITicketTypeService;
use App\Services\EventService;
use App\Services\TicketTypeService;
use App\Framework\View;

/**
 * Public, read-only browsing of festival events.
 *
 * index() — overview of all events for one event type (e.g. /events/jazz)
 * show()  — detail page for a single event (e.g. /event/12)
 */
class EventController
{
    private IEventService $eventService;
    private ITicketTypeService $ticketTypeService;

    public function __construct()
    {
        $this->eventService = new EventService();
        $this->ticketTypeService = new TicketTypeService();
    }

    // GET: /events/{type}
    public function index(array $vars = []): void
    {
        $typeSlug = (string)($vars['type'] ?? '');

        $eventType = $this->eventService->getTypeBySlug($typeSlug);
        if ($eventType === null) {
            http_response_code(404);
            echo 'Event type not found';
            return;
        }

        $events = $this->eventService->getByType($typeSlug);

        // All-access passes for this type, each with its purchasable options.
        $passes = [];
        foreach ($this->eventService->getPassesByType($typeSlug) as $passEvent) {
            $passes[] = [
                'event'   => $passEvent,
                'options' => $this->ticketTypeService->getActiveByEvent($passEvent->id),
            ];
        }

        View::render('Events/index', [
            'eventType'    => $eventType,   // ['slug'=>, 'name'=>, 'description'=>]
            'events'       => $events,
            'passes'       => $passes,
            'availability' => $this->eventService->getAvailabilityByType($typeSlug),
        ], $eventType['name']);
    }

    // GET: /event/{id}
    public function show(array $vars = []): void
    {
        $id = (int)($vars['id'] ?? 0);
        $event = $this->eventService->getById($id);

        if ($event === null || !$event->is_published) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        $ticketTypes = $this->ticketTypeService->getActiveByEvent($event->id);

        View::render('Events/detail', [
            'event'       => $event,
            'ticketTypes' => $ticketTypes,
        ], $event->title);
    }
}
