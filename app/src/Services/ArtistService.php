<?php
namespace App\Services;

use App\Models\ArtistModel;
use App\Repositories\ArtistRepository;
use App\Repositories\Interfaces\IArtistRepository;
use App\Services\Interfaces\IArtistService;

class ArtistService implements IArtistService
{
    private IArtistRepository $repo;

    public function __construct()
    {
        $this->repo = new ArtistRepository();
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
}
