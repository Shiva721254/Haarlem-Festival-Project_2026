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
    public function countAll(): int;
    /**
     * @param array<string,mixed> $post
     * @return array{ticket:TicketTypeModel,error:?string}
     */
    public function buildAdminFormModel(array $post): array;
    public function validateAdminForm(TicketTypeModel $ticket): ?string;
}
