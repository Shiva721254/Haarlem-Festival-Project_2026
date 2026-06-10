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
    private const GALLERY_DIR = __DIR__ . '/../../public/assets/uploads/artists/';
    private const GALLERY_PUBLIC = '/assets/uploads/artists/';
    private const ALLOWED_IMAGE = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_IMAGE_BYTES = 4 * 1024 * 1024; // 4 MB

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
        $back = '/admin/artists/edit/' . $artistId;
        if ($artistId <= 0) {
            Flash::error('Unknown artist.');
            header('Location: /admin/artists');
            exit();
        }

        if (!isset($_FILES['gallery_image']) || $_FILES['gallery_image']['error'] === UPLOAD_ERR_NO_FILE) {
            Flash::error('Please choose an image to upload.');
            header('Location: ' . $back);
            exit();
        }

        $file = $_FILES['gallery_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flash::error('Image upload failed; please try again.');
            header('Location: ' . $back);
            exit();
        }
        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            Flash::error('Image is too large (max 4 MB).');
            header('Location: ' . $back);
            exit();
        }
        // Trust the real MIME type, not the client filename.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGE[$mime])) {
            Flash::error('Only JPG, PNG or WEBP images are allowed.');
            header('Location: ' . $back);
            exit();
        }

        if (!is_dir(self::GALLERY_DIR)) {
            mkdir(self::GALLERY_DIR, 0775, true);
        }
        $filename = 'a' . $artistId . '_' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_IMAGE[$mime];
        if (!move_uploaded_file($file['tmp_name'], self::GALLERY_DIR . $filename)) {
            Flash::error('Could not save the uploaded image.');
            header('Location: ' . $back);
            exit();
        }

        $this->artistService->addImage($artistId, self::GALLERY_PUBLIC . $filename);
        Flash::success('Gallery image added.');
        header('Location: ' . $back);
        exit();
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
        header('Location: /admin/artists/edit/' . $artistId);
        exit();
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
        $artist->career_highlights = trim($_POST['career_highlights'] ?? '') ?: null;
        $artist->tracks = trim($_POST['tracks'] ?? '') ?: null;
        $artist->audio_url = trim($_POST['audio_url'] ?? '') ?: null;
        return $artist;
    }

    private function validate(ArtistModel $artist): ?string
    {
        return $artist->name === '' ? 'Artist name is required.' : null;
    }
}
