<?php
namespace App\Services\Interfaces;

interface IAdminService
{
    /** @return array<string,int> dashboard counts keyed for the admin view */
    public function getDashboardStats(): array;
}
