<?php
namespace App\Controllers;

use App\Services\Interfaces\IEventService;
use App\Framework\Http;
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

    public function __construct(IEventService $eventService)
    {
        $this->eventService = $eventService;
    }

    // GET: /events/{type}
    public function index(array $vars = []): void
    {
        $typeSlug = (string)($vars['type'] ?? '');
        $eventType = $this->eventService->getTypeBySlug($typeSlug);
        if ($eventType === null) {
            Http::notFound('Event type not found');
        }
        View::render('Events/index', $this->typeViewData($eventType, $typeSlug), $eventType['name']);
    }

    /** @return array<string,mixed> */
    private function typeViewData(array $eventType, string $typeSlug): array
    {
        return [
            'eventType'    => $eventType,
            'events'       => $this->eventService->getByType($typeSlug),
            'passes'       => $this->eventService->getPassesWithOptionsByType($typeSlug),
            'availability' => $this->eventService->getAvailabilityByType($typeSlug),
        ];
    }

    // GET: /event/{id}
    public function show(array $vars = []): void
    {
        $event = $this->eventService->getById((int)($vars['id'] ?? 0));
        if ($event === null || !$event->is_published) {
            Http::notFound('Event not found');
        }
        View::render('Events/detail', [
            'event'       => $event,
            'ticketTypes' => $this->eventService->getTicketOptionsForEvent($event->id),
        ], $event->title);
    }
}
