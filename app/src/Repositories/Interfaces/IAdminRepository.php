<?php
namespace App\Repositories\Interfaces;

interface IAdminRepository
{
    /** @return array<string,int> */
    public function dashboardCounts(): array;
}
