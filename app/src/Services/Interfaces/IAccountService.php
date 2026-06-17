<?php
namespace App\Services\Interfaces;

use App\Models\UserModel;

interface IAccountService
{
    public function getById(int $userId): ?UserModel;

    /**
     * @param array<string,mixed> $post
     * @return array{ok:bool,messages:array<int,array{ok:bool,message:string}>,firstName:?string}
     */
    public function updateFromRequest(int $userId, array $post): array;

    /** @return array{ok:bool,message:string} */
    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone, ?string $address): array;

    /**
     * Optional password change. Returns null when no fields were filled,
     * otherwise the outcome.
     *
     * @return array{ok:bool,message:string}|null
     */
    public function changePassword(int $userId, string $current, string $new, string $confirm): ?array;

    /** @return array{ok:bool,message:string}|null */
    public function uploadProfileImage(int $userId): ?array;

    /** @return array<string,mixed> GDPR data-access payload */
    public function buildDataExport(int $userId): array;

    public function deleteAccount(int $userId): void;
}
