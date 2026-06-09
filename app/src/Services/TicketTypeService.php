<?php
namespace App\Services;

use App\Models\TicketTypeModel;
use App\Repositories\TicketTypeRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Services\Interfaces\ITicketTypeService;

class TicketTypeService implements ITicketTypeService
{
    private ITicketTypeRepository $repo;

    public function __construct()
    {
        $this->repo = new TicketTypeRepository();
    }

    /** @return TicketTypeModel[] */
    public function getActiveByEvent(int $eventId): array
    {
        return $this->repo->getActiveByEvent($eventId);
    }

    /** @return TicketTypeModel[] */
    public function getByEvent(int $eventId): array
    {
        return $this->repo->getByEvent($eventId);
    }

    public function getById(int $id): ?TicketTypeModel
    {
        return $this->repo->getById($id);
    }

    public function create(TicketTypeModel $t): int
    {
        return $this->repo->create($t);
    }

    public function update(TicketTypeModel $t): void
    {
        $this->repo->update($t);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}
