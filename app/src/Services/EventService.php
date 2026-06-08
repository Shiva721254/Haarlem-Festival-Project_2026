<?php
namespace App\Services;

use App\Models\EventModel;
use App\Repositories\EventRepository;
use App\Repositories\Interfaces\IEventRepository;
use App\Services\Interfaces\IEventService;

class EventService implements IEventService
{
    private IEventRepository $eventRepository;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
    }

    public function getByType(string $typeSlug): array
    {
        return $this->eventRepository->getPublishedByType($typeSlug);
    }

    public function getById(int $id): ?EventModel
    {
        return $this->eventRepository->getById($id);
    }

    public function getActiveTypes(): array
    {
        return $this->eventRepository->getActiveTypes();
    }

    public function getTypeBySlug(string $slug): ?array
    {
        return $this->eventRepository->getTypeBySlug($slug);
    }
}
