<?php
namespace App\Services\Interfaces;

use App\Models\TicketTypeModel;

interface ITicketTypeService
{
    /** @return TicketTypeModel[] */
    public function getActiveByEvent(int $eventId): array;

    /** @return TicketTypeModel[] */
    public function getByEvent(int $eventId): array;

    public function getById(int $id): ?TicketTypeModel;

    public function create(TicketTypeModel $t): int;

    public function update(TicketTypeModel $t): void;

    public function delete(int $id): void;
}
