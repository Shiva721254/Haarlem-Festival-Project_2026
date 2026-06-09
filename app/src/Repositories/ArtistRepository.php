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
        return $row ? ArtistModel::fromDb($row) : null;
    }

    public function create(ArtistModel $artist): int
    {
        $sql = 'INSERT INTO artists (name, genre, bio, image)
                VALUES (:name, :genre, :bio, :image)';
        $this->execute($sql, $this->params($artist));
        return $this->lastInsertId();
    }

    public function update(ArtistModel $artist): void
    {
        $params = $this->params($artist);
        $params['id'] = $artist->id;
        $this->execute(
            'UPDATE artists SET name = :name, genre = :genre, bio = :bio, image = :image WHERE id = :id',
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
        ];
    }
}
