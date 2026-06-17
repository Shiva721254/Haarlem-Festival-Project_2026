<?php
namespace App\Services;

use App\Models\TicketTypeModel;
use App\Repositories\TicketTypeRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Services\Interfaces\ITicketTypeService;

class TicketTypeService implements ITicketTypeService
{
    private ITicketTypeRepository $repo;

    public function __construct(ITicketTypeRepository $repo)
    {
        $this->repo = $repo;
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

    public function countAll(): int
    {
        return $this->repo->countAll();
    }

    public function buildAdminFormModel(array $post): array
    {
        $ticket = $this->hydrate($post);
        return ['ticket' => $ticket, 'error' => $this->validateAdminForm($ticket)];
    }

    private function hydrate(array $post): TicketTypeModel
    {
        $ticket = new TicketTypeModel();
        $ticket->id = (int)($post['id'] ?? 0);
        $ticket->event_id = (int)($post['event_id'] ?? 0);
        $ticket->name = trim($post['name'] ?? '');
        $ticket->price = (float)($post['price'] ?? 0);
        $ticket->vat_rate = (float)($post['vat_rate'] ?? 21);
        $ticket->capacity = (int)($post['capacity'] ?? 0);
        $ticket->sold = 0;
        $ticket->is_active = !empty($post['is_active']);
        return $ticket;
    }

    public function validateAdminForm(TicketTypeModel $ticket): ?string
    {
        if ($ticket->name === '') {
            return 'Ticket name is required.';
        }
        if ($ticket->price < 0) {
            return 'Price cannot be negative.';
        }
        if ($ticket->capacity < 0) {
            return 'Capacity cannot be negative.';
        }
        return null;
    }
}
