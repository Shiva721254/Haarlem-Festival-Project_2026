<?php
namespace App\Models;

use App\Enums\UserRole;
class UserModel 
{
    public int $UserId;
    public string $Username;
    public string $FirstName;
    public string $LastName;
    public string $Email;    

    public UserRole $Role = UserRole::Customer;

    public bool $isVerified;
    public bool $isActive;

    public ?string $verification_token;
    public ?string $verification_token_expires_at;
    public ?string $verified_at;

    public string $Password = "";
    public ?string $reset_token_hash;
    public ?string $reset_token_expires_at;
    public ?string $profile_image = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $created_at = null;

    public static function fromDb(array $data): self
    {
        $user = new self();
        $user->fillIdentity($data);
        $user->fillAccountState($data);
        $user->fillOptionalProfile($data);
        return $user;
    }

    private function fillIdentity(array $data): void
    {
        $this->UserId = (int)$data['UserId'];
        $this->Username = $data['Username'] ?? '';
        $this->FirstName = $data['FirstName'];
        $this->LastName = $data['LastName'];
        $this->Email = $data['Email'];
        $this->Password = $data['Password'] ?? '';
        $this->Role = UserRole::from($data['Role']);
    }

    private function fillAccountState(array $data): void
    {
        $this->isVerified = (bool)($data['isVerified'] ?? false);
        $this->isActive = (bool)($data['isActive'] ?? false);
        $this->verification_token = $data['verification_token'] ?? null;
        $this->verification_token_expires_at = $data['verification_token_expires_at'] ?? null;
        $this->verified_at = $data['verified_at'] ?? null;
        $this->reset_token_hash = $data['reset_token_hash'] ?? null;
        $this->reset_token_expires_at = $data['reset_token_expires_at'] ?? null;
    }

    private function fillOptionalProfile(array $data): void
    {
        $this->profile_image = $data['profile_image'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }

    public function fromPost(): UserModel
    {
        $user = new UserModel();
        $user->fillPostIdentity();
        $user->fillPostState($this->Role);
        return $user;
    }

    private function fillPostIdentity(): void
    {
        $this->UserId = isset($_POST['UserId']) ? (int)$_POST['UserId'] : 0;
        $this->Username = trim($_POST['Username'] ?? '');
        $this->FirstName = $_POST['FirstName'];
        $this->LastName = $_POST['LastName'];
        $this->Email = $_POST['Email'];
        $this->Password = $_POST['Password'] ?? $_POST['password'] ?? '';
    }

    private function fillPostState(UserRole $fallbackRole): void
    {
        $this->Role = isset($_POST['Role']) 
            ? UserRole::from($_POST['Role']) 
            : $fallbackRole;
        $this->isVerified = isset($_POST['isVerified']) ? (bool)$_POST['isVerified'] : false;
        $this->isActive = isset($_POST['isActive']) ? (bool)$_POST['isActive'] : false;
    }
}
