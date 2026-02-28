<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IArtistImageRepository;
use \PDO;

class ArtistImageRepository extends Repository implements IArtistImageRepository
{
    public function addImage(int $artist_id, string $image_path): void
    {
        try {
            $sql = 'INSERT INTO artist_images (artist_id, image_path) VALUES (:artist_id, :image_path)';
            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(':artist_id', $artist_id, PDO::PARAM_INT);
            $stmt->bindValue(':image_path', $image_path, PDO::PARAM_STR);

            $stmt->execute();
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            error_log('Error adding artist image: ' . $e->getMessage());
             throw new \Exception('Failed to add artist image. Please try again later.');
        }        
    }
}