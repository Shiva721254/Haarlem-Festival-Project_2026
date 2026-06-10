<?php
namespace App\Services\Interfaces;

use App\Models\UserModel;

interface IAccountService
{
    public function getById(int $userId): ?UserModel;

    /** @return array{ok:bool,message:string} */
    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone, ?string $address): array;

    /**
     * Optional password change. Returns null when no fields were filled,
     * otherwise the outcome.
     *
     * @return array{ok:bool,message:string}|null
     */
    public function changePassword(int $userId, string $current, string $new, string $confirm): ?array;

    public function updateProfileImage(int $userId, string $path): void;

    /** @return array<string,mixed> GDPR data-access payload */
    public function buildDataExport(int $userId): array;

    public function deleteAccount(int $userId): void;
}
