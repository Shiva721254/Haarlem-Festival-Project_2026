<?php
namespace App\Services\Interfaces;
use App\Models\UserModel;

interface IUserService
{
    // CRUD
    public function getFilteredUsers(?string $term = null, ?string $role = null): array;
    public function create(UserModel $article) : void;
    public function getById(int $id): ?UserModel;
    public function update(UserModel $article) : void;
    public function delete(int $id): void;

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
    function sendUpdateNotification(string $email, array $changes): bool;
}