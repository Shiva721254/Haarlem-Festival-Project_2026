<?php
namespace App\Models;

class EventModel
{
    public int $id;
    public int $event_type_id;
    public ?int $venue_id = null;
    public ?int $restaurant_id = null;
    public string $title;
    public ?string $description = null;
    public ?string $image = null;
    public string $starts_at;
    public ?string $ends_at = null;
    public bool $is_published = false;
    public bool $is_pass = false;

    // Joined / related data (populated by the repository when available).
    public ?string $event_type_name = null;
    public ?string $event_type_slug = null;
    public ?VenueModel $venue = null;
    public ?RestaurantModel $restaurant = null;
    /** @var ArtistModel[] */
    public array $artists = [];
    /** @var int[] */
    public array $artist_ids = [];

    public static function fromDb(array $data): self
    {
        $e = new self();
        $e->fillCore($data);
        $e->fillSchedule($data);
        $e->fillJoinedType($data);
        return $e;
    }

    private function fillCore(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->event_type_id = (int)$data['event_type_id'];
        $this->venue_id = isset($data['venue_id']) ? (int)$data['venue_id'] : null;
        $this->restaurant_id = isset($data['restaurant_id']) ? (int)$data['restaurant_id'] : null;
        $this->title = $data['title'];
    }

    private function fillSchedule(array $data): void
    {
        $this->description = $data['description'] ?? null;
        $this->image = $data['image'] ?? null;
        $this->starts_at = $data['starts_at'];
        $this->ends_at = $data['ends_at'] ?? null;
        $this->is_published = (bool)($data['is_published'] ?? false);
        $this->is_pass = (bool)($data['is_pass'] ?? false);
    }

    private function fillJoinedType(array $data): void
    {
        $this->event_type_name = $data['event_type_name'] ?? null;
        $this->event_type_slug = $data['event_type_slug'] ?? null;
    }
}
