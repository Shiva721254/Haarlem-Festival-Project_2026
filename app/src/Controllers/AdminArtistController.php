<?php
namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\Http;
use App\Framework\Redirect;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IArtistService;

class AdminArtistController
{
    private IArtistService $artistService;

    public function __construct(IArtistService $artistService)
    {
        $this->artistService = $artistService;
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
        $form = $this->artistService->buildAdminFormModel($_POST);
        if ($error = $form['error']) {
            $this->renderForm($form['artist'], $error, 'New artist');
        }
        $this->artistService->create($form['artist']);
        $this->saved('Artist created.');
    }

    public function edit(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $artist = $this->artistService->getById((int)($vars['id'] ?? 0));
        if ($artist === null) {
            Http::notFound('Artist not found');
        }
        View::renderAdmin('Admin/artists/form', [
            'artist'  => $artist,
            'gallery' => $this->artistService->getGallery($artist->id),
        ], 'Edit artist');
    }

    // POST: /admin/artists/{id}/images — add a gallery image.
    public function uploadImage(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();
        $artistId = (int)($vars['id'] ?? 0);
        if ($artistId <= 0) {
            Flash::error('Unknown artist.');
            Redirect::to('/admin/artists');
        }
        Flash::results([$this->artistService->uploadGalleryImage($artistId)]);
        Redirect::to('/admin/artists/edit/' . $artistId);
    }

    // POST: /admin/artists/images/delete — remove a gallery image.
    public function deleteImage(): void
    {
        AuthMiddleware::requireAdmin();
        $imageId = (int)($_POST['image_id'] ?? 0);
        $artistId = (int)($_POST['artist_id'] ?? 0);
        if ($imageId > 0) {
            $this->artistService->deleteImage($imageId);
            Flash::success('Gallery image removed.');
        }
        Redirect::to('/admin/artists/edit/' . $artistId);
    }

    public function update(): void
    {
        AuthMiddleware::requireAdmin();
        $form = $this->artistService->buildAdminFormModel($_POST);
        $artist = $form['artist'];
        if (($error = $form['error']) || $artist->id <= 0) {
            $this->renderForm($artist, $error ?? 'Invalid artist.', 'Edit artist');
        }
        $this->artistService->update($artist);
        $this->saved('Artist updated.');
    }

    public function delete(): void
    {
        AuthMiddleware::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->artistService->delete($id);
            Flash::success('Artist deleted.');
        }
        Redirect::to('/admin/artists');
    }

    /** Re-render the form with an error and stop. */
    private function renderForm(?object $artist, string $error, string $title): never
    {
        Flash::error($error);
        View::renderAdmin('Admin/artists/form', ['artist' => $artist], $title);
        exit();
    }

    private function saved(string $message): never
    {
        Flash::success($message);
        Redirect::to('/admin/artists');
    }
}
