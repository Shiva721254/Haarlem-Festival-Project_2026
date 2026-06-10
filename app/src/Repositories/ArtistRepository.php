<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ArtistModel;
use App\Repositories\Interfaces\IArtistRepository;

class ArtistRepository extends Repository implements IArtistRepository
{
    /** @return ArtistModel[] */
    public function getAll(): array
    {
        $rows = $this->fetchAll('SELECT * FROM artists ORDER BY name');
        return array_map(static fn(array $row) => ArtistModel::fromDb($row), $rows);
    }

    public function getById(int $id): ?ArtistModel
    {
        $row = $this->fetchOne('SELECT * FROM artists WHERE id = :id', ['id' => $id]);
        if ($row === null) {
            return null;
        }
        $artist = ArtistModel::fromDb($row);
        $artist->images = $this->loadImages($id);
        return $artist;
    }

    /**
     * The artist's appearances during the festival (from event_artist).
     *
     * @return array<int,array{id:int,title:string,type_name:string,starts_at:string,ends_at:?string,venue_name:?string}>
     */
    public function getSchedule(int $artistId): array
    {
        $sql = 'SELECT e.id, e.title, et.name AS type_name, e.starts_at, e.ends_at, v.name AS venue_name
                FROM events e
                JOIN event_artist ea ON ea.event_id = e.id
                JOIN event_types et ON et.id = e.event_type_id
                LEFT JOIN venues v ON v.id = e.venue_id
                WHERE ea.artist_id = :id AND e.is_published = 1
                ORDER BY e.starts_at';
        return $this->fetchAll($sql, ['id' => $artistId]);
    }

    /** @return string[] */
    private function loadImages(int $artistId): array
    {
        $rows = $this->fetchAll(
            'SELECT path FROM artist_images WHERE artist_id = :id ORDER BY sort_order, id',
            ['id' => $artistId]
        );
        return array_map(static fn(array $r) => $r['path'], $rows);
    }

    /**
     * Gallery rows with ids, for admin management.
     *
     * @return array<int,array{id:int,path:string}>
     */
    public function getGallery(int $artistId): array
    {
        return $this->fetchAll(
            'SELECT id, path FROM artist_images WHERE artist_id = :id ORDER BY sort_order, id',
            ['id' => $artistId]
        );
    }

    public function addImage(int $artistId, string $path): void
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 AS next FROM artist_images WHERE artist_id = :id',
            ['id' => $artistId]
        );
        $this->execute(
            'INSERT INTO artist_images (artist_id, path, sort_order) VALUES (:a, :p, :s)',
            ['a' => $artistId, 'p' => $path, 's' => (int)($row['next'] ?? 1)]
        );
    }

    public function deleteImage(int $imageId): void
    {
        $this->execute('DELETE FROM artist_images WHERE id = :id', ['id' => $imageId]);
    }

    public function create(ArtistModel $artist): int
    {
        $sql = 'INSERT INTO artists (name, genre, bio, image, career_highlights, tracks, audio_url)
                VALUES (:name, :genre, :bio, :image, :career_highlights, :tracks, :audio_url)';
        $this->execute($sql, $this->params($artist));
        return $this->lastInsertId();
    }

    public function update(ArtistModel $artist): void
    {
        $params = $this->params($artist);
        $params['id'] = $artist->id;
        $this->execute(
            'UPDATE artists SET name = :name, genre = :genre, bio = :bio, image = :image,
                    career_highlights = :career_highlights, tracks = :tracks, audio_url = :audio_url
             WHERE id = :id',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM artists WHERE id = :id', ['id' => $id]);
    }

    /** @return array<string,mixed> */
    private function params(ArtistModel $artist): array
    {
        return [
            'name' => $artist->name,
            'genre' => $artist->genre,
            'bio' => $artist->bio,
            'image' => $artist->image,
            'career_highlights' => $artist->career_highlights,
            'tracks' => $artist->tracks,
            'audio_url' => $artist->audio_url,
        ];
    }
}
