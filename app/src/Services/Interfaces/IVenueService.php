<?php
namespace App\Services\Interfaces;

use App\Models\VenueModel;

interface IVenueService
{
    /** @return VenueModel[] */
    public function getAll(): array;
    public function getById(int $id): ?VenueModel;
    public function create(VenueModel $venue): int;
    public function update(VenueModel $venue): void;
    public function delete(int $id): void;
}
