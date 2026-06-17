<?php

namespace App\Controllers;

use App\Services\Interfaces\IEventService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Http;
use App\Framework\Redirect;
use App\Middleware\AuthMiddleware;

/**
 * Admin CRUD for events. All actions require an admin session.
 */
class AdminEventController
{
    private IEventService $eventService;

    public function __construct(IEventService $eventService)
    {
        $this->eventService = $eventService;
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
        $this->renderForm(null, null, 'New event');
    }

    // POST: /admin/events
    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->eventService->buildAdminFormModel($_POST);
        $error = $form['error'] ?? $form['uploadError'];
        if ($error !== null) {
            $this->renderForm($form['event'], $error, 'New event');
        }
        $this->eventService->create($form['event']);
        $this->saved('Event created.');
    }

    // GET: /admin/events/edit/{id}
    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $event = $this->eventService->getById((int)($vars['id'] ?? 0));
        if ($event === null) {
            Http::notFound('Event not found');
        }
        $this->renderForm($event, null, 'Edit event');
    }

    // POST: /admin/events/update
    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->eventService->buildAdminFormModel($_POST);
        $event = $form['event'];
        $error = $form['error'] ?? $form['uploadError'];
        if ($error !== null || $event->id <= 0) {
            $this->renderForm($event, $error ?? 'Invalid event.', 'Edit event');
        }
        $this->eventService->update($event);
        $this->saved('Event updated.');
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
        Redirect::to('/admin/events');
    }

    /** Render the create/edit form (optionally with an error) and stop. */
    private function renderForm(?object $event, ?string $error, string $title): never
    {
        if ($error !== null) {
            Flash::error($error);
        }
        View::renderAdmin('Admin/events/form', [
            'event'   => $event,
            'options' => $this->eventService->getFormOptions(),
        ], $title);
        exit();
    }

    private function saved(string $message): never
    {
        Flash::success($message);
        Redirect::to('/admin/events');
    }
}
