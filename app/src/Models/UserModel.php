<?php
namespace App\Models;

use App\Enums\UserRole;
class UserModel 
{
    public int $UserId;
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
    public ?string $created_at = null;

    public static function fromDb(array $data): self
    {
        $user = new self();
        $user->UserId = (int)$data['UserId'];
        $user->FirstName = $data['FirstName'];
        $user->LastName = $data['LastName'];
        $user->Email = $data['Email'];
        $user->Password = $data['Password'] ?? '';
        
        // Convert strings from DB back to Enums
        $user->Role = UserRole::from($data['Role']);
        
        // Ensure boolean types
        $user->isVerified = (bool)($data['isVerified'] ?? false);
        $user->isActive = (bool)($data['isActive'] ?? false);

        $user->verification_token = $data['verification_token'] ?? null;
        $user->verification_token_expires_at = $data['verification_token_expires_at'] ?? null;
        $user->verified_at = $data['verified_at'] ?? null;

        // These will be null if the user hasn't requested a reset
        $user->reset_token_hash = $data['reset_token_hash'] ?? null;
        $user->reset_token_expires_at = $data['reset_token_expires_at'] ?? null;
        $user->profile_image = $data['profile_image'] ?? null;
        $user->created_at = $data['created_at'] ?? null;

        return $user;
    }

    public function fromPost(): UserModel
    {
        $user = new UserModel();
        $user->UserId = isset($_POST['UserId']) ? (int)$_POST['UserId'] : 0;
        $user->FirstName = $_POST['FirstName'];
        $user->LastName = $_POST['LastName'];
        $user->Email = $_POST['Email'];

        // Handle both cases for password field
        $user->Password = $_POST['Password'] ?? $_POST['password'] ?? '';

        $user->Role = isset($_POST['Role']) 
            ? UserRole::from($_POST['Role']) 
            : $this->Role;
        
        $user->isVerified = isset($_POST['isVerified']) ? (bool)$_POST['isVerified'] : false;
        $user->isActive = isset($_POST['isActive']) ? (bool)$_POST['isActive'] : false;
        return $user;
    }
}
