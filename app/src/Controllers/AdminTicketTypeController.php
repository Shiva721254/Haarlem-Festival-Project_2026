<?php

namespace App\Controllers;

use App\Services\Interfaces\ITicketTypeService;
use App\Services\Interfaces\IEventService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Http;
use App\Framework\Redirect;
use App\Middleware\AuthMiddleware;

/**
 * Admin management of ticket types, scoped to an event.
 */
class AdminTicketTypeController
{
    private ITicketTypeService $ticketService;
    private IEventService $eventService;

    public function __construct(ITicketTypeService $ticketService, IEventService $eventService)
    {
        $this->ticketService = $ticketService;
        $this->eventService = $eventService;
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
        $this->renderForm($event, null, null, 'New ticket type');
    }

    // POST: /admin/tickets
    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->ticketService->buildAdminFormModel($_POST);
        $ticket = $form['ticket'];
        $event = $this->requireEvent($ticket->event_id);
        if ($error = $form['error']) {
            $this->renderForm($event, $ticket, $error, 'New ticket type');
        }
        $this->ticketService->create($ticket);
        $this->saved($event->id, 'Ticket type created.');
    }

    // GET: /admin/tickets/edit/{id}
    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $ticket = $this->ticketService->getById((int)($vars['id'] ?? 0));
        if ($ticket === null) {
            Http::notFound('Ticket type not found');
        }
        $event = $this->requireEvent($ticket->event_id);
        $this->renderForm($event, $ticket, null, 'Edit ticket type');
    }

    // POST: /admin/tickets/update
    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->ticketService->buildAdminFormModel($_POST);
        $ticket = $form['ticket'];
        $event = $this->requireEvent($ticket->event_id);
        if (($error = $form['error']) || $ticket->id <= 0) {
            $this->renderForm($event, $ticket, $error ?? 'Invalid ticket type.', 'Edit ticket type');
        }
        $this->ticketService->update($ticket);
        $this->saved($event->id, 'Ticket type updated.');
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
        Redirect::to($this->ticketsUrl($eventId));
    }

    /** Load the scoping event or 404. */
    private function requireEvent(int $eventId): object
    {
        $event = $this->eventService->getById($eventId);
        if ($event === null) {
            Http::notFound('Event not found');
        }
        return $event;
    }

    /** Render the ticket form (optionally with an error) and stop. */
    private function renderForm(object $event, ?object $ticket, ?string $error, string $title): never
    {
        if ($error !== null) {
            Flash::error($error);
        }
        View::renderAdmin('Admin/tickets/form', ['event' => $event, 'ticket' => $ticket], $title);
        exit();
    }

    private function saved(int $eventId, string $message): never
    {
        Flash::success($message);
        Redirect::to($this->ticketsUrl($eventId));
    }

    private function ticketsUrl(int $eventId): string
    {
        return '/admin/events/' . $eventId . '/tickets';
    }
}
