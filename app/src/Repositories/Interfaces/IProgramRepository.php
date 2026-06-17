<?php
namespace App\Repositories\Interfaces;

interface IProgramRepository
{
    /** @return \App\Models\ProgramItemModel[] */
    public function getForUser(int $userId): array;
}
