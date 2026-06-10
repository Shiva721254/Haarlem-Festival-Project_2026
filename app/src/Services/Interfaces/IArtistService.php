<?php
namespace App\Services\Interfaces;

use App\Models\ArtistModel;

interface IArtistService
{
    /** @return ArtistModel[] */
    public function getAll(): array;
    public function getById(int $id): ?ArtistModel;
    /** @return array<int,array{id:int,title:string,type_name:string,starts_at:string,ends_at:?string,venue_name:?string}> */
    public function getSchedule(int $artistId): array;
    /** @return array<int,array{id:int,path:string}> */
    public function getGallery(int $artistId): array;
    public function addImage(int $artistId, string $path): void;
    public function deleteImage(int $imageId): void;
    public function create(ArtistModel $artist): int;
    public function update(ArtistModel $artist): void;
    public function delete(int $id): void;
}
