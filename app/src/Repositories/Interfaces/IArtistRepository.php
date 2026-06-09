<?php
namespace App\Repositories\Interfaces;

use App\Models\ArtistModel;

interface IArtistRepository
{
    /** @return ArtistModel[] */
    public function getAll(): array;
    public function getById(int $id): ?ArtistModel;
    public function create(ArtistModel $artist): int;
    public function update(ArtistModel $artist): void;
    public function delete(int $id): void;
}
