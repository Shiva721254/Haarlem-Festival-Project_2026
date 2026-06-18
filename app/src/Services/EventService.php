<?php
namespace App\Services;

use App\Models\EventModel;
use App\Framework\ImageUpload;
use App\Repositories\Interfaces\IEventRepository;
use App\Repositories\Interfaces\ITicketTypeRepository;
use App\Services\Interfaces\IEventService;

class EventService implements IEventService
{
    private IEventRepository $eventRepository;
    private ITicketTypeRepository $ticketTypeRepository;

    public function __construct(IEventRepository $eventRepository, ITicketTypeRepository $ticketTypeRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->ticketTypeRepository = $ticketTypeRepository;
    }

    public function getByType(string $typeSlug): array
    {
        return $this->eventRepository->getPublishedByType($typeSlug);
    }

    public function getPassesByType(string $typeSlug): array
    {
        return $this->eventRepository->getPassesByType($typeSlug);
    }

    public function getPassesWithOptionsByType(string $typeSlug): array
    {
        return array_map(fn($event) => [
            'event' => $event,
            'options' => $this->ticketTypeRepository->getActiveByEvent($event->id),
        ], $this->getPassesByType($typeSlug));
    }

    public function getAvailabilityByType(string $typeSlug): array
    {
        return $this->eventRepository->getAvailabilityByType($typeSlug);
    }

    public function getById(int $id): ?EventModel
    {
        return $this->eventRepository->getById($id);
    }

    public function getTicketOptionsForEvent(int $eventId): array
    {
        return $this->ticketTypeRepository->getActiveByEvent($eventId);
    }

    public function getActiveTypes(): array
    {
        return $this->eventRepository->getActiveTypes();
    }

    public function getHomeSummaries(): array
    {
        return $this->eventRepository->getHomeSummaries();
    }

    public function getPassSummaries(): array
    {
        return $this->eventRepository->getPassSummaries();
    }

    public function getGroupedPassSummaries(): array
    {
        return $this->groupBy($this->getPassSummaries(), 'type_name');
    }

    public function getScheduleSummary(): array
    {
        return $this->eventRepository->getScheduleSummary();
    }

    public function getGroupedScheduleSummary(): array
    {
        return $this->groupBy($this->getScheduleSummary(), 'day');
    }

    private function groupBy(array $rows, string $key): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row[$key]][] = $row;
        }
        return $grouped;
    }

    public function getTypeBySlug(string $slug): ?array
    {
        return $this->eventRepository->getTypeBySlug($slug);
    }

    // --- Admin CRUD ---

    public function getAllForAdmin(): array
    {
        return $this->eventRepository->getAllForAdmin();
    }

    public function create(EventModel $event): int
    {
        return $this->eventRepository->create($event);
    }

    public function update(EventModel $event): void
    {
        $this->eventRepository->update($event);
    }

    public function delete(int $id): void
    {
        $this->eventRepository->delete($id);
    }

    public function buildAdminFormModel(array $post): array
    {
        $event = $this->hydrate($post);
        $image = ImageUpload::resolve('image_file', 'events');
        if ($image['path'] !== null) {
            $event->image = $image['path'];
        }
        return ['event' => $event, 'error' => $this->validateAdminForm($event), 'uploadError' => $image['error']];
    }

    private function hydrate(array $post): EventModel
    {
        $event = new EventModel();
        $event->id = (int)($post['id'] ?? 0);
        $event->event_type_id = (int)($post['event_type_id'] ?? 0);
        $event->venue_id = !empty($post['venue_id']) ? (int)$post['venue_id'] : null;
        $event->restaurant_id = !empty($post['restaurant_id']) ? (int)$post['restaurant_id'] : null;
        $event->title = trim($post['title'] ?? '');
        $event->description = trim($post['description'] ?? '') ?: null;
        $event->image = trim($post['image'] ?? '') ?: null;
        $this->hydrateSchedule($event, $post);
        return $event;
    }

    private function hydrateSchedule(EventModel $event, array $post): void
    {
        $event->starts_at = trim($post['starts_at'] ?? '');
        $event->ends_at = trim($post['ends_at'] ?? '') ?: null;
        $event->is_published = !empty($post['is_published']);
        $event->artist_ids = array_map('intval', $post['artist_ids'] ?? []);
    }

    public function validateAdminForm(EventModel $event): ?string
    {
        if ($event->title === '') {
            return 'Title is required.';
        }
        if ($event->event_type_id <= 0) {
            return 'Please choose an event type.';
        }
        return $this->validateDates($event);
    }

    private function validateDates(EventModel $event): ?string
    {
        if ($event->starts_at === '' || strtotime($event->starts_at) === false) {
            return 'A valid start date/time is required.';
        }
        if ($event->ends_at !== null && strtotime($event->ends_at) === false) {
            return 'The end date/time is invalid.';
        }
        return null;
    }

    /** @return array<int,array{id:int,name:string}> */
    public function getFormOptions(): array
    {
        return [
            'types'       => $this->eventRepository->getTypeOptions(),
            'venues'      => $this->eventRepository->getVenueOptions(),
            'restaurants' => $this->eventRepository->getRestaurantOptions(),
            'artists'     => $this->eventRepository->getArtistOptions(),
        ];
    }
}
