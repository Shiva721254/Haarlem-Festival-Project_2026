<?php
namespace App\Services\Interfaces;

use App\Models\EventModel;

interface IEventService
{
    /**
     * @return EventModel[]
     */
    public function getByType(string $typeSlug): array;

    /**
     * Pass "events" (all-access passes) for an event type.
     *
     * @return EventModel[]
     */
    public function getPassesByType(string $typeSlug): array;

    public function getById(int $id): ?EventModel;

    /**
     * @return array<int,array{slug:string,name:string}>
     */
    public function getActiveTypes(): array;

    /** @return array<int,array{slug:string,name:string,description:?string,from_price:?string}> */
    public function getHomeSummaries(): array;

    /** @return array<int,array{type_name:string,slug:string,option_name:string,price:string}> */
    public function getPassSummaries(): array;

    /**
     * @return array{slug:string,name:string,description:?string}|null
     */
    public function getTypeBySlug(string $slug): ?array;

    // --- Admin CRUD ---

    /** @return EventModel[] */
    public function getAllForAdmin(): array;

    public function create(EventModel $event): int;

    public function update(EventModel $event): void;

    public function delete(int $id): void;

    /**
     * Option lists for the event form selects.
     *
     * @return array{types:array,venues:array,restaurants:array,artists:array}
     */
    public function getFormOptions(): array;
}
