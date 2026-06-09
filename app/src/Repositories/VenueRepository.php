<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\VenueModel;
use App\Repositories\Interfaces\IVenueRepository;

class VenueRepository extends Repository implements IVenueRepository
{
    /** @return VenueModel[] */
    public function getAll(): array
    {
        $rows = $this->fetchAll('SELECT * FROM venues ORDER BY name');
        return array_map(static fn(array $row) => VenueModel::fromDb($row), $rows);
    }

    public function getById(int $id): ?VenueModel
    {
        $row = $this->fetchOne('SELECT * FROM venues WHERE id = :id', ['id' => $id]);
        return $row ? VenueModel::fromDb($row) : null;
    }

    public function create(VenueModel $venue): int
    {
        $sql = 'INSERT INTO venues (name, address, capacity, description, image)
                VALUES (:name, :address, :capacity, :description, :image)';
        $this->execute($sql, $this->params($venue));
        return $this->lastInsertId();
    }

    public function update(VenueModel $venue): void
    {
        $params = $this->params($venue);
        $params['id'] = $venue->id;
        $this->execute(
            'UPDATE venues SET name = :name, address = :address, capacity = :capacity,
                description = :description, image = :image WHERE id = :id',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM venues WHERE id = :id', ['id' => $id]);
    }

    /** @return array<string,mixed> */
    private function params(VenueModel $venue): array
    {
        return [
            'name' => $venue->name,
            'address' => $venue->address,
            'capacity' => $venue->capacity,
            'description' => $venue->description,
            'image' => $venue->image,
        ];
    }
}
