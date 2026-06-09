<?php

namespace App\Controllers;

use App\Services\TicketTypeService;
use App\Services\EventService;
use App\Services\Interfaces\ITicketTypeService;
use App\Services\Interfaces\IEventService;
use App\Models\TicketTypeModel;
use App\Framework\View;
use App\Framework\Flash;
use App\Middleware\AuthMiddleware;

/**
 * Admin management of ticket types, scoped to an event.
 */
class AdminTicketTypeController
{
    private ITicketTypeService $ticketService;
    private IEventService $eventService;

    public function __construct()
    {
        $this->ticketService = new TicketTypeService();
        $this->eventService = new EventService();
    }

    // GET: /admin/events/{eventId}/tickets
    public function index(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $event = $this->requireEvent((int)($vars['eventId'] ?? 0));

        View::renderAdmin('Admin/tickets/index', [
            'event'   => $event,
            'tickets' => $this->ticketService->getByEvent($event->id),
        ], 'Tickets — ' . $event->title);
    }

    // GET: /admin/events/{eventId}/tickets/create
    public function create(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $event = $this->requireEvent((int)($vars['eventId'] ?? 0));

        View::renderAdmin('Admin/tickets/form', [
            'event'  => $event,
            'ticket' => null,
        ], 'New ticket type');
    }

    // POST: /admin/tickets
    public function store(): void
    {
        AuthMiddleware::requireAdmin();

        $ticket = $this->buildFromPost();
        $event = $this->requireEvent($ticket->event_id);

        if ($error = $this->validate($ticket)) {
            Flash::error($error);
            View::renderAdmin('Admin/tickets/form', ['event' => $event, 'ticket' => $ticket], 'New ticket type');
            return;
        }

        $this->ticketService->create($ticket);
        Flash::success('Ticket type created.');
        header('Location: /admin/events/' . $event->id . '/tickets');
        exit();
    }

    // GET: /admin/tickets/edit/{id}
    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();

        $ticket = $this->ticketService->getById((int)($vars['id'] ?? 0));
        if ($ticket === null) {
            http_response_code(404);
            echo 'Ticket type not found';
            return;
        }
        $event = $this->requireEvent($ticket->event_id);

        View::renderAdmin('Admin/tickets/form', ['event' => $event, 'ticket' => $ticket], 'Edit ticket type');
    }

    // POST: /admin/tickets/update
    public function update(): void
    {
        AuthMiddleware::requireAdmin();

        $ticket = $this->buildFromPost();
        $ticket->id = (int)($_POST['id'] ?? 0);
        $event = $this->requireEvent($ticket->event_id);

        if (($error = $this->validate($ticket)) || $ticket->id <= 0) {
            Flash::error($error ?? 'Invalid ticket type.');
            View::renderAdmin('Admin/tickets/form', ['event' => $event, 'ticket' => $ticket], 'Edit ticket type');
            return;
        }

        $this->ticketService->update($ticket);
        Flash::success('Ticket type updated.');
        header('Location: /admin/events/' . $event->id . '/tickets');
        exit();
    }

    // POST: /admin/tickets/delete
    public function delete(): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int)($_POST['id'] ?? 0);
        $eventId = (int)($_POST['event_id'] ?? 0);
        if ($id > 0) {
            $this->ticketService->delete($id);
            Flash::success('Ticket type deleted.');
        }
        header('Location: /admin/events/' . $eventId . '/tickets');
        exit();
    }

    private function buildFromPost(): TicketTypeModel
    {
        $t = new TicketTypeModel();
        $t->event_id  = (int)($_POST['event_id'] ?? 0);
        $t->name      = trim($_POST['name'] ?? '');
        $t->price     = (float)($_POST['price'] ?? 0);
        $t->vat_rate  = (float)($_POST['vat_rate'] ?? 21);
        $t->capacity  = (int)($_POST['capacity'] ?? 0);
        $t->sold      = 0;
        $t->is_active = !empty($_POST['is_active']);
        return $t;
    }

    private function validate(TicketTypeModel $t): ?string
    {
        if ($t->name === '') {
            return 'Ticket name is required.';
        }
        if ($t->price < 0) {
            return 'Price cannot be negative.';
        }
        if ($t->capacity < 0) {
            return 'Capacity cannot be negative.';
        }
        return null;
    }

    /**
     * Load an event or 404. Returns the event model.
     */
    private function requireEvent(int $eventId): \App\Models\EventModel
    {
        $event = $this->eventService->getById($eventId);
        if ($event === null) {
            http_response_code(404);
            echo 'Event not found';
            exit();
        }
        return $event;
    }
}
