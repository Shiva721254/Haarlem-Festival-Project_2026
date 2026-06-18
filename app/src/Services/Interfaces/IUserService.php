<?php
namespace App\Services\Interfaces;
use App\Models\UserModel;

interface IUserService
{
    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array ;
    public function create(UserModel $article) : void;
    public function createWithPasswordConfirmation(UserModel $user, string $confirm): void;
    /**
     * @param array<string,mixed> $post
     * @return array{ok:bool,user:UserModel,error:?string}
     */
    public function saveAdminUser(array $post): array;
    /** @return array{question:string,answer:int} */
    public function registrationCaptchaChallenge(): array;
    public function verifyRegistrationCaptcha(string $answer, ?int $expected): bool;
    /**
     * @param array<string,string> $fields
     * @return array{ok:bool,error?:string}
     */
    public function registerCustomer(array $fields, string $password, string $confirm): array;
    public function getById(int $id): ?UserModel;
    public function update(UserModel $article) : void;
    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone = null, ?string $address = null): void;
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool;
    public function updateProfileImage(int $userId, string $path): void;
    public function delete(int $id): void;
    public function deleteOwnAccount(int $userId): void;

    public function isStrongPassword(string $password): bool;

    //login stuff
    public function authenticate(string $identifier, string $password): ?UserModel;

    //password stuff
    public function validateResetToken(string $token): ?UserModel;
    public function sendPasswordReset(string $email): bool ;
    public function completePasswordReset(string $token, string $password): bool;

    // verification stuff
    public function sendVerificationEmail(string $email): bool;
    public function validateVerificationToken(string $token) : ?UserModel;
    public function completeAccountVerification(string $token) :bool;
}
