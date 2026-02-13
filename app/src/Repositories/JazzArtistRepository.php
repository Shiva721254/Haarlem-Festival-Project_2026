<?php

namespace App\Repositories\Interfaces;

use App\Framework\Repository;
use App\Repositories\Interfaces\IJazzArtistRepository;
use App\Models\JazzArtistModel;
use \PDO;

class JazzArtistRepository extends Repository implements IJazzArtistRepository 
{
    public function getAllJazzArtists(): array
    {
        $sql = 'SELECT a.artist_id, a.artist_name, a.description 
            FROM jazz_artists a
            LEFT JOIN jazz_event e ON a.artist_id = e.artist_id
            GROUP BY a.artist_id, a.artist_name, a.description
            ORDER BY MIN(e.event_time) ASC';

        $result = $this->getConnection()->query($sql);

        return array_map(
            fn($row) => JazzArtistModel::fromDb($row), 
            $result->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    // <--- CRUD OPERATIONS --->

    public function getJazzArtistById(int $id): ?JazzArtistModel
    {
        $sql = 'SELECT artist_id, artist_name, description 
                FROM jazz_artists WHERE artist_id = :id';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? JazzArtistModel::fromDb($data) : null;
    }

    public function addJazzArtist(JazzArtistModel $artist): bool
    {
        $sql = 'INSERT INTO jazz_artists (artist_name, description) 
                VALUES (:artist_name, :description)';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':artist_name', $artist->artist_name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $artist->description, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function updateJazzArtist(JazzArtistModel $artist): bool
    {
        $sql = 'UPDATE jazz_artists 
                SET artist_name = :artist_name, description = :description 
                WHERE artist_id = :id';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':artist_name', $artist->artist_name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $artist->description, PDO::PARAM_STR);
        $stmt->bindValue(':id', $artist->artist_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteJazzArtist(int $id): bool
    {
        $sql = 'DELETE FROM jazz_artists WHERE artist_id = :id';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}