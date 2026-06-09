<?php
namespace App\Repositories\Interfaces;

use App\Models\EventModel;

interface IEventRepository
{
    /**
     * Published events for a given event-type slug, soonest first.
     *
     * @return EventModel[]
     */
    public function getPublishedByType(string $typeSlug): array;

    /**
     * A single event with its venue, restaurant and artists loaded.
     */
    public function getById(int $id): ?EventModel;

    /**
     * All active event types, e.g. for navigation.
     *
     * @return array<int,array{slug:string,name:string}>
     */
    public function getActiveTypes(): array;

    /**
     * A single active event type by slug, for the overview hero/header.
     *
     * @return array{slug:string,name:string,description:?string}|null
     */
    public function getTypeBySlug(string $slug): ?array;

    // --- Admin CRUD ---

    /** @return EventModel[] */
    public function getAllForAdmin(): array;

    public function create(EventModel $event): int;

    public function update(EventModel $event): void;

    public function delete(int $id): void;

    /** @return array<int,array{id:int,name:string}> */
    public function getTypeOptions(): array;

    /** @return array<int,array{id:int,name:string}> */
    public function getVenueOptions(): array;

    /** @return array<int,array{id:int,name:string}> */
    public function getRestaurantOptions(): array;

    /** @return array<int,array{id:int,name:string}> */
    public function getArtistOptions(): array;
}
