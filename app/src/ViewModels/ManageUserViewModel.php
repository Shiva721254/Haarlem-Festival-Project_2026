<?php
namespace App\ViewModels;

use App\Models\UserModel;
use App\Enums\UserRole;

class ManageUserViewModel
{
    public ?UserModel $user;
    // For C#'s IEnumerable<SelectListItem>, use a simple array for view options.
    // The array will hold key-value pairs (value => display name) or objects.
    /** @var array<string|int, string> */
    public array $userRoleOptions;

    // Using simple arrays to hold the enum values, replacing C#'s Enum.GetValues().
    /** @var array<UserRole> */
    public array $userRoles;
    
    // The constructor is used for initialization logic.
    public function __construct(?UserModel $user = null)
    {
        $this->user = $user;
        // Populate the arrays with Enum cases
        $this->userRoles = UserRole::cases();

        // Populate the select box options (assuming static methods exist on Enums)
        $this->userRoleOptions = UserRole::toSelectOptions();
    }
}