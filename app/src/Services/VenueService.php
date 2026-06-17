<?php
namespace App\Services;

use App\Models\VenueModel;
use App\Framework\ImageUpload;
use App\Repositories\Interfaces\IVenueRepository;
use App\Repositories\VenueRepository;
use App\Services\Interfaces\IVenueService;

class VenueService implements IVenueService
{
    private IVenueRepository $repo;

    public function __construct(IVenueRepository $repo)
    {
        $this->repo = $repo;
    }

    /** @return VenueModel[] */
    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function getFestivalLocations(): array
    {
        return $this->repo->getFestivalLocations();
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

    public function buildAdminFormModel(array $post): array
    {
        $venue = $this->hydrate($post);
        $image = ImageUpload::resolve('image_file', 'venues');
        if ($image['path'] !== null) {
            $venue->image = $image['path'];
        }
        return ['venue' => $venue, 'error' => $this->validateAdminForm($venue), 'uploadError' => $image['error']];
    }

    private function hydrate(array $post): VenueModel
    {
        $venue = new VenueModel();
        $venue->id = (int)($post['id'] ?? 0);
        $venue->name = trim($post['name'] ?? '');
        $venue->address = trim($post['address'] ?? '') ?: null;
        $venue->capacity = ($post['capacity'] ?? '') !== '' ? (int)$post['capacity'] : null;
        $venue->description = trim($post['description'] ?? '') ?: null;
        $venue->image = trim($post['image'] ?? '') ?: null;
        return $venue;
    }

    public function validateAdminForm(VenueModel $venue): ?string
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
