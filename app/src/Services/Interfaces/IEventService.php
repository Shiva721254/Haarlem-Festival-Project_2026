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

    /** @return array<int,array{event:EventModel,options:array}> */
    public function getPassesWithOptionsByType(string $typeSlug): array;

    /** @return array<int,int> event_id => tickets available */
    public function getAvailabilityByType(string $typeSlug): array;

    public function getById(int $id): ?EventModel;

    public function getTicketOptionsForEvent(int $eventId): array;

    /**
     * @return array<int,array{slug:string,name:string}>
     */
    public function getActiveTypes(): array;

    /** @return array<int,array{slug:string,name:string,description:?string,from_price:?string}> */
    public function getHomeSummaries(): array;

    /** @return array<int,array{type_name:string,slug:string,option_name:string,price:string}> */
    public function getPassSummaries(): array;

    /** @return array<string,array<int,array<string,mixed>>> */
    public function getGroupedPassSummaries(): array;

    /** @return array<int,array{day:string,type_name:string,slug:string,sessions:int,first_t:string,last_t:string}> */
    public function getScheduleSummary(): array;

    /** @return array<string,array<int,array<string,mixed>>> */
    public function getGroupedScheduleSummary(): array;

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
     * @param array<string,mixed> $post
     * @return array{event:EventModel,error:?string,uploadError:?string}
     */
    public function buildAdminFormModel(array $post): array;

    public function validateAdminForm(EventModel $event): ?string;

    /**
     * Option lists for the event form selects.
     *
     * @return array{types:array,venues:array,restaurants:array,artists:array}
     */
    public function getFormOptions(): array;
}
