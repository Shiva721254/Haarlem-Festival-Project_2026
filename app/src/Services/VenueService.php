<?php
namespace App\Services;

use App\Models\VenueModel;
use App\Repositories\Interfaces\IVenueRepository;
use App\Repositories\VenueRepository;
use App\Services\Interfaces\IVenueService;

class VenueService implements IVenueService
{
    private IVenueRepository $repo;

    public function __construct()
    {
        $this->repo = new VenueRepository();
    }

    /** @return VenueModel[] */
    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getById(int $id): ?VenueModel
    {
        return $this->repo->getById($id);
    }

    public function create(VenueModel $venue): int
    {
        return $this->repo->create($venue);
    }

    public function update(VenueModel $venue): void
    {
        $this->repo->update($venue);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}
