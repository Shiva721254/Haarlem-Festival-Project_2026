<?php
namespace App\Repositories\Interfaces;

use App\Models\UserModel;

interface IUserRepository
{
    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array ;
    public function create(UserModel $article) : void;
    public function getById(int $id): ?UserModel;
    public function update(UserModel $article) : void;
    public function updateProfile(int $userId, string $firstName, string $lastName, string $email): void;
    public function updateProfileImage(int $userId, string $path): void;
    public function delete(int $id): void;
    public function getByEmail(string $email): ?UserModel;

    // password
    public function updateResetToken(int $userId, ?string $hash, ?string $expiry): void;
    public function findByResetToken(string $hash): ?UserModel;
    public function updatePassword(int $userId, string $passwordHash): void;

    // verification
    public function updateVerifyToken(int $userId, ?string $hash, ?string $expiry): void;
    public function findByVerifyToken(string $hash): ?UserModel;
    public function verifyAccount(int $userId): bool;
}