<?php
namespace App\Services\Interfaces;

use App\Models\EventModel;

interface IEventService
{
    /**
     * @return EventModel[]
     */
    public function getByType(string $typeSlug): array;

    public function getById(int $id): ?EventModel;

    /**
     * @return array<int,array{slug:string,name:string}>
     */
    public function getActiveTypes(): array;

    /**
     * @return array{slug:string,name:string,description:?string}|null
     */
    public function getTypeBySlug(string $slug): ?array;
}
