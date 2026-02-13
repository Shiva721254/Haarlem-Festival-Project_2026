<?php

namespace App\Services\Interfaces;

use App\Models\JazzArtistModel;

interface IJazzArtistService 
{
    public function getAllArtistsSortedByEvent(): array;
    public function getArtist(int $id): ?JazzArtistModel;
    public function createArtist(JazzArtistModel $artist): bool;
    public function updateArtist(JazzArtistModel $artist): bool;
    public function removeArtist(int $id): bool;
}