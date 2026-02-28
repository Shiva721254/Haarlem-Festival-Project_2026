<?php

namespace App\Services;

use App\Models\JazzArtistModel;
use App\Repositories\Interfaces\IJazzArtistRepository;
use App\Services\Interfaces\IJazzArtistService;
use Exception;

class JazzArtistService implements IJazzArtistService
{
    private IJazzArtistRepository $repository;

    public function __construct(IJazzArtistRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllArtistsSortedByEvent(): array
    {
        try {
            return $this->repository->getAllJazzArtists();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getArtist(int $id): JazzArtistModel
    {
        $artist = $this->repository->getJazzArtistById($id);
        
        if (!$artist) {
            throw new Exception("Jazz Artist with ID $id not found.");
        }
        
        return $artist;
    }

    public function createArtist(JazzArtistModel $artist): bool
    {
        // Business Logic: Name cannot be empty
        if (empty(trim($artist->artist_name))) {
            throw new Exception("Artist name is required.");
        }

        try {
            return $this->repository->addJazzArtist($artist);
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateArtist(JazzArtistModel $artist): bool
    {
        $existing = $this->repository->getJazzArtistById($artist->artist_id);
        if (!$existing) {
            throw new Exception("Cannot update: Artist does not exist.");
        }

        try {
            return $this->repository->updateJazzArtist($artist);
        } catch (Exception $e) {
            return false;
        }
    }

    public function removeArtist(int $id): bool
    {
        try {
            return $this->repository->deleteJazzArtist($id);
        } catch (Exception $e) {
            return false;
        }
    }
}