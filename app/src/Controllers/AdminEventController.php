<?php

namespace App\Controllers;

use App\Services\Interfaces\IEventService;
use App\Services\EventService;
use App\Models\EventModel;
use App\Framework\View;
use App\Framework\Flash;
use App\Middleware\AuthMiddleware;

/**
 * Admin CRUD for events. All actions require an admin session.
 */
class AdminEventController
{
    private IEventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

    // GET: /admin/events
    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        $events = $this->eventService->getAllForAdmin();
        View::renderAdmin('Admin/events/index', ['events' => $events], 'Events');
    }

    // GET: /admin/events/create
    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/events/form', [
            'event'   => null,                          // null = creating
            'options' => $this->eventService->getFormOptions(),
        ], 'New event');
    }

    // POST: /admin/events
    public function store(): void
    {
        AuthMiddleware::requireAdmin();

        $event = $this->buildFromPost();
        $error = $this->validate($event);

        if ($error !== null) {
            Flash::error($error);
            View::renderAdmin('Admin/events/form', [
                'event'   => $event,
                'options' => $this->eventService->getFormOptions(),
            ], 'New event');
            return;
        }

        $this->eventService->create($event);
        Flash::success('Event created.');
        header('Location: /admin/events');
        exit();
    }

    // GET: /admin/events/edit/{id}
    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int)($vars['id'] ?? 0);
        $event = $this->eventService->getById($id);
        if ($event === null) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }

        View::renderAdmin('Admin/events/form', [
            'event'   => $event,
            'options' => $this->eventService->getFormOptions(),
        ], 'Edit event');
    }

    // POST: /admin/events/update
    public function update(): void
    {
        AuthMiddleware::requireAdmin();

        $event = $this->buildFromPost();
        $event->id = (int)($_POST['id'] ?? 0);

        $error = $this->validate($event);
        if ($error !== null || $event->id <= 0) {
            Flash::error($error ?? 'Invalid event.');
            View::renderAdmin('Admin/events/form', [
                'event'   => $event,
                'options' => $this->eventService->getFormOptions(),
            ], 'Edit event');
            return;
        }

        $this->eventService->update($event);
        Flash::success('Event updated.');
        header('Location: /admin/events');
        exit();
    }

    // POST: /admin/events/delete
    public function delete(): void
    {
        AuthMiddleware::requireAdmin();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->eventService->delete($id);
            Flash::success('Event deleted.');
        }
        header('Location: /admin/events');
        exit();
    }

    /**
     * Build an EventModel from the submitted form fields.
     */
    private function buildFromPost(): EventModel
    {
        $event = new EventModel();
        $event->event_type_id = (int)($_POST['event_type_id'] ?? 0);
        $event->venue_id      = !empty($_POST['venue_id']) ? (int)$_POST['venue_id'] : null;
        $event->restaurant_id = !empty($_POST['restaurant_id']) ? (int)$_POST['restaurant_id'] : null;
        $event->title         = trim($_POST['title'] ?? '');
        $event->description   = trim($_POST['description'] ?? '') ?: null;
        $event->image         = trim($_POST['image'] ?? '') ?: null;
        $event->starts_at     = trim($_POST['starts_at'] ?? '');
        $event->ends_at       = trim($_POST['ends_at'] ?? '') ?: null;
        $event->is_published  = !empty($_POST['is_published']);
        return $event;
    }

    /**
     * Server-side validation. Returns an error message or null when valid.
     */
    private function validate(EventModel $event): ?string
    {
        if ($event->title === '') {
            return 'Title is required.';
        }
        if ($event->event_type_id <= 0) {
            return 'Please choose an event type.';
        }
        if ($event->starts_at === '' || strtotime($event->starts_at) === false) {
            return 'A valid start date/time is required.';
        }
        if ($event->ends_at !== null && strtotime($event->ends_at) === false) {
            return 'The end date/time is invalid.';
        }
        return null;
    }
}
