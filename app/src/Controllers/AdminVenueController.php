<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Models\VenueModel;
use App\Services\Interfaces\IVenueService;
use App\Services\VenueService;

class AdminVenueController
{
    private IVenueService $venueService;

    public function __construct()
    {
        $this->venueService = new VenueService();
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
        $venue = $this->buildFromPost();

        if ($error = $this->validate($venue)) {
            Flash::error($error);
            View::renderAdmin('Admin/venues/form', ['venue' => $venue], 'New venue');
            return;
        }

        $this->venueService->create($venue);
        Flash::success('Venue created.');
        header('Location: /admin/venues');
        exit();
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $venue = $this->venueService->getById((int)($vars['id'] ?? 0));
        if ($venue === null) {
            http_response_code(404);
            echo 'Venue not found';
            return;
        }

        View::renderAdmin('Admin/venues/form', ['venue' => $venue], 'Edit venue');
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $venue = $this->buildFromPost();
        $venue->id = (int)($_POST['id'] ?? 0);

        if (($error = $this->validate($venue)) || $venue->id <= 0) {
            Flash::error($error ?? 'Invalid venue.');
            View::renderAdmin('Admin/venues/form', ['venue' => $venue], 'Edit venue');
            return;
        }

        $this->venueService->update($venue);
        Flash::success('Venue updated.');
        header('Location: /admin/venues');
        exit();
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->venueService->delete($id);
            Flash::success('Venue deleted.');
        }
        header('Location: /admin/venues');
        exit();
    }

    private function buildFromPost(): VenueModel
    {
        $venue = new VenueModel();
        $venue->name = trim($_POST['name'] ?? '');
        $venue->address = trim($_POST['address'] ?? '') ?: null;
        $venue->capacity = ($_POST['capacity'] ?? '') !== '' ? (int)$_POST['capacity'] : null;
        $venue->description = trim($_POST['description'] ?? '') ?: null;
        $venue->image = trim($_POST['image'] ?? '') ?: null;
        $upload = \App\Framework\ImageUpload::handle('image_file', 'venues');
        if (!empty($upload['path'])) {
            $venue->image = $upload['path'];
        } elseif (!$upload['ok']) {
            \App\Framework\Flash::error($upload['message']);
        }
        return $venue;
    }

    private function validate(VenueModel $venue): ?string
    {
        if ($venue->name === '') {
            return 'Venue name is required.';
        }
        if ($venue->capacity !== null && $venue->capacity < 0) {
            return 'Capacity cannot be negative.';
        }
        return null;
    }
}
