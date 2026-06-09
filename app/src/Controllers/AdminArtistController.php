<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Models\ArtistModel;
use App\Services\ArtistService;
use App\Services\Interfaces\IArtistService;

class AdminArtistController
{
    private IArtistService $artistService;

    public function __construct()
    {
        $this->artistService = new ArtistService();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/artists/index', [
            'artists' => $this->artistService->getAll(),
        ], 'Artists');
    }

    public function create(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/artists/form', ['artist' => null], 'New artist');
    }

    public function store(): void
    {
        AuthMiddleware::requireAdmin();
        $artist = $this->buildFromPost();

        if ($error = $this->validate($artist)) {
            Flash::error($error);
            View::renderAdmin('Admin/artists/form', ['artist' => $artist], 'New artist');
            return;
        }

        $this->artistService->create($artist);
        Flash::success('Artist created.');
        header('Location: /admin/artists');
        exit();
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $artist = $this->artistService->getById((int)($vars['id'] ?? 0));
        if ($artist === null) {
            http_response_code(404);
            echo 'Artist not found';
            return;
        }

        View::renderAdmin('Admin/artists/form', ['artist' => $artist], 'Edit artist');
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $artist = $this->buildFromPost();
        $artist->id = (int)($_POST['id'] ?? 0);

        if (($error = $this->validate($artist)) || $artist->id <= 0) {
            Flash::error($error ?? 'Invalid artist.');
            View::renderAdmin('Admin/artists/form', ['artist' => $artist], 'Edit artist');
            return;
        }

        $this->artistService->update($artist);
        Flash::success('Artist updated.');
        header('Location: /admin/artists');
        exit();
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->artistService->delete($id);
            Flash::success('Artist deleted.');
        }
        header('Location: /admin/artists');
        exit();
    }

    private function buildFromPost(): ArtistModel
    {
        $artist = new ArtistModel();
        $artist->name = trim($_POST['name'] ?? '');
        $artist->genre = trim($_POST['genre'] ?? '') ?: null;
        $artist->bio = trim($_POST['bio'] ?? '') ?: null;
        $artist->image = trim($_POST['image'] ?? '') ?: null;
        return $artist;
    }

    private function validate(ArtistModel $artist): ?string
    {
        return $artist->name === '' ? 'Artist name is required.' : null;
    }
}
