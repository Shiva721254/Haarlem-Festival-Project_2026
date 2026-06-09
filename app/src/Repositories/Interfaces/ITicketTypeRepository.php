<?php
namespace App\Repositories\Interfaces;

use App\Models\TicketTypeModel;

interface ITicketTypeRepository
{
    /** @return TicketTypeModel[] */
    public function getActiveByEvent(int $eventId): array;

    /** @return TicketTypeModel[] */
    public function getByEvent(int $eventId): array;

    public function getById(int $id): ?TicketTypeModel;

    public function create(TicketTypeModel $t): int;

    public function update(TicketTypeModel $t): void;

    public function delete(int $id): void;

    public function incrementSold(int $id, int $quantity): void;
}
