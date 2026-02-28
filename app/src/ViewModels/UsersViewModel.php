<?php
namespace App\ViewModels;
use App\Models\UserModel;
use App\Enums\UserRole; 

class UsersViewModel
{
    /** @var UserModel[] */
    public array $users;
    public ?string $searchTerm;
    public ?string $roleFilter;

    public function __construct(array $users, ?string $searchTerm = null, ?string $roleFilter = null)
    {
        $this->users = $users;
        $this->searchTerm = $searchTerm;
        $this->roleFilter = $roleFilter;
    }
}