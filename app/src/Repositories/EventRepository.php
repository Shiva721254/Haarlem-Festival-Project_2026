<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IEventRepository;
use App\Models\EventModel;
use App\Models\VenueModel;
use App\Models\RestaurantModel;
use App\Models\ArtistModel;

class EventRepository extends Repository implements IEventRepository
{
    public function getPublishedByType(string $typeSlug): array
    {
        $sql = 'SELECT e.*, et.name AS event_type_name, et.slug AS event_type_slug
                FROM events e
                JOIN event_types et ON et.id = e.event_type_id
                WHERE et.slug = :slug AND e.is_published = 1
                ORDER BY e.starts_at ASC';

        $rows = $this->fetchAll($sql, ['slug' => $typeSlug]);

        $events = [];
        foreach ($rows as $row) {
            $events[] = EventModel::fromDb($row);
        }
        return $events;
    }

    public function getById(int $id): ?EventModel
    {
        $sql = 'SELECT e.*, et.name AS event_type_name, et.slug AS event_type_slug
                FROM events e
                JOIN event_types et ON et.id = e.event_type_id
                WHERE e.id = :id';

        $row = $this->fetchOne($sql, ['id' => $id]);
        if ($row === null) {
            return null;
        }

        $event = EventModel::fromDb($row);
        $event->venue = $this->loadVenue($event->venue_id);
        $event->restaurant = $this->loadRestaurant($event->restaurant_id);
        $event->artists = $this->loadArtists($event->id);
        return $event;
    }

    public function getActiveTypes(): array
    {
        $sql = 'SELECT slug, name FROM event_types WHERE is_active = 1 ORDER BY name';
        return $this->fetchAll($sql);
    }

    public function getTypeBySlug(string $slug): ?array
    {
        $sql = 'SELECT slug, name, description FROM event_types WHERE slug = :slug AND is_active = 1';
        return $this->fetchOne($sql, ['slug' => $slug]);
    }

    // --- Admin CRUD -------------------------------------------------------

    /**
     * All events (published or not) for the admin list, newest start first.
     *
     * @return EventModel[]
     */
    public function getAllForAdmin(): array
    {
        $sql = 'SELECT e.*, et.name AS event_type_name, et.slug AS event_type_slug
                FROM events e
                JOIN event_types et ON et.id = e.event_type_id
                ORDER BY e.starts_at DESC';

        $events = [];
        foreach ($this->fetchAll($sql) as $row) {
            $events[] = EventModel::fromDb($row);
        }
        return $events;
    }

    public function create(EventModel $event): int
    {
        $sql = 'INSERT INTO events
                    (event_type_id, venue_id, restaurant_id, title, description, image, starts_at, ends_at, is_published)
                VALUES
                    (:event_type_id, :venue_id, :restaurant_id, :title, :description, :image, :starts_at, :ends_at, :is_published)';

        $this->execute($sql, $this->toParams($event));
        return $this->lastInsertId();
    }

    public function update(EventModel $event): void
    {
        $sql = 'UPDATE events SET
                    event_type_id = :event_type_id,
                    venue_id = :venue_id,
                    restaurant_id = :restaurant_id,
                    title = :title,
                    description = :description,
                    image = :image,
                    starts_at = :starts_at,
                    ends_at = :ends_at,
                    is_published = :is_published
                WHERE id = :id';

        $params = $this->toParams($event);
        $params['id'] = $event->id;
        $this->execute($sql, $params);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM events WHERE id = :id', ['id' => $id]);
    }

    /**
     * Event-type options for form selects.
     *
     * @return array<int,array{id:int,name:string}>
     */
    public function getTypeOptions(): array
    {
        return $this->fetchAll('SELECT id, name FROM event_types ORDER BY name');
    }

    /**
     * @return array<int,array{id:int,name:string}>
     */
    public function getVenueOptions(): array
    {
        return $this->fetchAll('SELECT id, name FROM venues ORDER BY name');
    }

    /**
     * @return array<int,array{id:int,name:string}>
     */
    public function getRestaurantOptions(): array
    {
        return $this->fetchAll('SELECT id, name FROM restaurants ORDER BY name');
    }

    /**
     * Map an EventModel to bound query parameters.
     *
     * @return array<string,mixed>
     */
    private function toParams(EventModel $event): array
    {
        return [
            'event_type_id' => $event->event_type_id,
            'venue_id'      => $event->venue_id,
            'restaurant_id' => $event->restaurant_id,
            'title'         => $event->title,
            'description'   => $event->description,
            'image'         => $event->image,
            'starts_at'     => $event->starts_at,
            'ends_at'       => $event->ends_at !== '' ? $event->ends_at : null,
            'is_published'  => $event->is_published ? 1 : 0,
        ];
    }

    private function loadVenue(?int $venueId): ?VenueModel
    {
        if ($venueId === null) {
            return null;
        }
        $row = $this->fetchOne('SELECT * FROM venues WHERE id = :id', ['id' => $venueId]);
        return $row ? VenueModel::fromDb($row) : null;
    }

    private function loadRestaurant(?int $restaurantId): ?RestaurantModel
    {
        if ($restaurantId === null) {
            return null;
        }
        $row = $this->fetchOne('SELECT * FROM restaurants WHERE id = :id', ['id' => $restaurantId]);
        return $row ? RestaurantModel::fromDb($row) : null;
    }

    /**
     * @return ArtistModel[]
     */
    private function loadArtists(int $eventId): array
    {
        $sql = 'SELECT a.* FROM artists a
                JOIN event_artist ea ON ea.artist_id = a.id
                WHERE ea.event_id = :eid
                ORDER BY a.name';
        $rows = $this->fetchAll($sql, ['eid' => $eventId]);

        $artists = [];
        foreach ($rows as $row) {
            $artists[] = ArtistModel::fromDb($row);
        }
        return $artists;
    }
}
