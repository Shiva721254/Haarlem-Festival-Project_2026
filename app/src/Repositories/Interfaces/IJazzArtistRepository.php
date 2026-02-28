<?php

namespace App\Repositories\Interfaces;

use App\Models\JazzArtistModel;

interface IJazzArtistRepository 
{
    public function getAllJazzArtists(): array;
    public function getJazzArtistById(int $id): ?JazzArtistModel;
    public function addJazzArtist(JazzArtistModel $artist): bool;
    public function updateJazzArtist(JazzArtistModel $artist): bool;
    public function deleteJazzArtist(int $id): bool;
}