<?php
namespace App\ViewModels;
use App\Models\UserModel;

class UsersViewModel
{
    /** @var UserModel[] */
    public array $users;

    public function __construct(array $users)
    {
        $this->users = $users;
    }
}