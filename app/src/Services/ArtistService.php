<?php
namespace App\Services;

use App\Models\ArtistModel;
use App\Framework\ImageUpload;
use App\Repositories\ArtistRepository;
use App\Repositories\Interfaces\IArtistRepository;
use App\Services\Interfaces\IArtistService;

class ArtistService implements IArtistService
{
    private IArtistRepository $repo;

    public function __construct(IArtistRepository $repo)
    {
        $this->repo = $repo;
    }

    /** @return ArtistModel[] */
    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): ?ArtistModel
    {
        return $this->repo->getById($id);
    }

    /** @return array<int,array{id:int,title:string,type_name:string,starts_at:string,ends_at:?string,venue_name:?string}> */
    public function getSchedule(int $artistId): array
    {
        return $this->repo->getSchedule($artistId);
    }

    /** @return array<int,array{id:int,path:string}> */
    public function getGallery(int $artistId): array
    {
        return $this->repo->getGallery($artistId);
    }

    public function addImage(int $artistId, string $path): void
    {
        $this->repo->addImage($artistId, $path);
    }

    public function deleteImage(int $imageId): void
    {
        $this->repo->deleteImage($imageId);
    }

    public function create(ArtistModel $artist): int
    {
        return $this->repo->create($artist);
    }

    public function update(ArtistModel $artist): void
    {
        $this->repo->update($artist);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }

    public function buildAdminFormModel(array $post): array
    {
        $artist = $this->hydrate($post);
        return ['artist' => $artist, 'error' => $this->validateAdminForm($artist)];
    }

    private function hydrate(array $post): ArtistModel
    {
        $artist = new ArtistModel();
        $artist->id = (int)($post['id'] ?? 0);
        $artist->name = trim($post['name'] ?? '');
        $artist->genre = trim($post['genre'] ?? '') ?: null;
        $artist->bio = trim($post['bio'] ?? '') ?: null;
        $artist->image = trim($post['image'] ?? '') ?: null;
        $artist->career_highlights = trim($post['career_highlights'] ?? '') ?: null;
        $artist->tracks = trim($post['tracks'] ?? '') ?: null;
        $artist->audio_url = trim($post['audio_url'] ?? '') ?: null;
        return $artist;
    }

    public function validateAdminForm(ArtistModel $artist): ?string
    {
        return $artist->name === '' ? 'Artist name is required.' : null;
    }

    public function uploadGalleryImage(int $artistId): array
    {
        if ($artistId <= 0) {
            return ['ok' => false, 'message' => 'Unknown artist.'];
        }
        $result = ImageUpload::handle('gallery_image', 'artists');
        $error = $this->galleryUploadError($result);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        $this->addImage($artistId, $result['path']);
        return ['ok' => true, 'message' => 'Gallery image added.'];
    }

    private function galleryUploadError(array $result): ?string
    {
        if (!$result['ok']) {
            return $result['message'];
        }
        if (!isset($result['path'])) {
            return 'Please choose an image to upload.';
        }
        return null;
    }
}
