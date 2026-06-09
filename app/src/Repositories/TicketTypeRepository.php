<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Models\TicketTypeModel;

class TicketTypeRepository extends Repository implements ITicketTypeRepository
{
    /**
     * Active ticket types for an event (what a visitor can buy).
     *
     * @return TicketTypeModel[]
     */
    public function getActiveByEvent(int $eventId): array
    {
        $sql = 'SELECT * FROM ticket_types WHERE event_id = :eid AND is_active = 1 ORDER BY price';
        return $this->mapAll($this->fetchAll($sql, ['eid' => $eventId]));
    }

    /**
     * All ticket types for an event (admin view).
     *
     * @return TicketTypeModel[]
     */
    public function getByEvent(int $eventId): array
    {
        $sql = 'SELECT * FROM ticket_types WHERE event_id = :eid ORDER BY price';
        return $this->mapAll($this->fetchAll($sql, ['eid' => $eventId]));
    }

    public function getById(int $id): ?TicketTypeModel
    {
        // Join the event type so callers can apply type-specific pricing rules
        // (e.g. the HaarlemPas reduction on Stories).
        $row = $this->fetchOne(
            'SELECT tt.*, et.slug AS event_type_slug
             FROM ticket_types tt
             JOIN events e ON e.id = tt.event_id
             JOIN event_types et ON et.id = e.event_type_id
             WHERE tt.id = :id',
            ['id' => $id]
        );
        return $row ? TicketTypeModel::fromDb($row) : null;
    }

    public function create(TicketTypeModel $t): int
    {
        $sql = 'INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, sold, is_active)
                VALUES (:event_id, :name, :price, :vat_rate, :capacity, 0, :is_active)';
        $this->execute($sql, [
            'event_id'  => $t->event_id,
            'name'      => $t->name,
            'price'     => $t->price,
            'vat_rate'  => $t->vat_rate,
            'capacity'  => $t->capacity,
            'is_active' => $t->is_active ? 1 : 0,
        ]);
        return $this->lastInsertId();
    }

    public function update(TicketTypeModel $t): void
    {
        $sql = 'UPDATE ticket_types
                SET name = :name, price = :price, vat_rate = :vat_rate,
                    capacity = :capacity, is_active = :is_active
                WHERE id = :id';
        $this->execute($sql, [
            'name'      => $t->name,
            'price'     => $t->price,
            'vat_rate'  => $t->vat_rate,
            'capacity'  => $t->capacity,
            'is_active' => $t->is_active ? 1 : 0,
            'id'        => $t->id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM ticket_types WHERE id = :id', ['id' => $id]);
    }

    /**
     * Increase the sold count (called when an order is paid). Guards against
     * overselling by only updating while stock remains.
     */
    public function incrementSold(int $id, int $quantity): void
    {
        $this->execute(
            'UPDATE ticket_types SET sold = sold + :q WHERE id = :id AND sold + :q <= capacity',
            ['q' => $quantity, 'id' => $id]
        );
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return TicketTypeModel[]
     */
    private function mapAll(array $rows): array
    {
        return array_map(static fn(array $r) => TicketTypeModel::fromDb($r), $rows);
    }
}
