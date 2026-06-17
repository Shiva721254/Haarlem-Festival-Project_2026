<?php
namespace App\Repositories\Interfaces;

use App\Models\UserModel;

interface IAccountRepository
{
    public function getById(int $userId): ?UserModel;

    public function updateProfileImage(int $userId, string $path): void;

    /** @return array<int,array<string,mixed>> */
    public function getOrderExportRows(int $userId): array;
}
