<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\Http;
use App\Framework\Redirect;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IVenueService;

class AdminVenueController
{
    private IVenueService $venueService;

    public function __construct(IVenueService $venueService)
    {
        $this->venueService = $venueService;
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/venues/index', ['venues' => $this->venueService->getAll()], 'Venues');
    }

    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/venues/form', ['venue' => null], 'New venue');
    }

    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->venueService->buildAdminFormModel($_POST);
        if ($error = ($form['error'] ?? $form['uploadError'])) {
            $this->renderForm($form['venue'], $error, 'New venue');
        }
        $this->venueService->create($form['venue']);
        $this->saved('Venue created.');
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $venue = $this->venueService->getById((int)($vars['id'] ?? 0));
        if ($venue === null) {
            Http::notFound('Venue not found');
        }
        View::renderAdmin('Admin/venues/form', ['venue' => $venue], 'Edit venue');
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->venueService->buildAdminFormModel($_POST);
        $venue = $form['venue'];
        if (($error = ($form['error'] ?? $form['uploadError'])) || $venue->id <= 0) {
            $this->renderForm($venue, $error ?? 'Invalid venue.', 'Edit venue');
        }
        $this->venueService->update($venue);
        $this->saved('Venue updated.');
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->venueService->delete($id);
            Flash::success('Venue deleted.');
        }
        Redirect::to('/admin/venues');
    }

    /** Re-render the form with an error and stop. */
    private function renderForm(?object $venue, string $error, string $title): never
    {
        Flash::error($error);
        View::renderAdmin('Admin/venues/form', ['venue' => $venue], $title);
        exit();
    }

    private function saved(string $message): never
    {
        Flash::success($message);
        Redirect::to('/admin/venues');
    }
}
