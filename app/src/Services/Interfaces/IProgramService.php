<?php
namespace App\Services\Interfaces;

interface IProgramService
{
    /**
     * The events a user holds paid tickets for, chronologically.
     *
     * @return \App\Models\ProgramItemModel[]
     */
    public function getForUser(int $userId): array;
}
