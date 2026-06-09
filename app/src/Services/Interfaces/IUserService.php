<?php
namespace App\Services\Interfaces;
use App\Models\UserModel;

interface IUserService
{
    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array ;
    public function create(UserModel $article) : void;
    public function getById(int $id): ?UserModel;
    public function update(UserModel $article) : void;
    public function updateProfile(int $userId, string $firstName, string $lastName, string $email): void;
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool;
    public function updateProfileImage(int $userId, string $path): void;
    public function delete(int $id): void;
    public function deleteOwnAccount(int $userId): void;

    //login stuff
    public function authenticate(string $email, string $password): ?UserModel;

    //password stuff
    public function validateResetToken(string $token): ?UserModel;
    public function sendPasswordReset(string $email): bool ;
    public function completePasswordReset(string $token, string $password): bool;

    // verification stuff
    public function sendVerificationEmail(string $email): bool;
    public function validateVerificationToken(string $token) : ?UserModel;
    public function completeAccountVerification(string $token) :bool;
}