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
        $e->id = (int)$data['id'];
        $e->event_type_id = (int)$data['event_type_id'];
        $e->venue_id = isset($data['venue_id']) ? (int)$data['venue_id'] : null;
        $e->restaurant_id = isset($data['restaurant_id']) ? (int)$data['restaurant_id'] : null;
        $e->title = $data['title'];
        $e->description = $data['description'] ?? null;
        $e->image = $data['image'] ?? null;
        $e->starts_at = $data['starts_at'];
        $e->ends_at = $data['ends_at'] ?? null;
        $e->is_published = (bool)($data['is_published'] ?? false);

        // Optional joined columns (aliased in the repository query).
        $e->event_type_name = $data['event_type_name'] ?? null;
        $e->event_type_slug = $data['event_type_slug'] ?? null;

        return $e;
    }
}
