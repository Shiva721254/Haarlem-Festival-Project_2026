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
