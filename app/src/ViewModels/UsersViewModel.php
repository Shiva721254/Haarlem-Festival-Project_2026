<?php
namespace App\ViewModels;
use App\Models\UserModel;

class UsersViewModel
{
    /** @var UserModel[] */
    public array $users;
    public string $search;
    public string $role;
    public string $sort;
    public string $dir;

    public function __construct(array $users, string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC')
    {
        $this->users = $users;
        $this->search = $search;
        $this->role = $role;
        $this->sort = $sort;
        $this->dir = $dir;
    }

    /**
     * Build a /users query string for a sort link on $column, toggling the
     * direction if it's the active column, preserving the current search/role.
     */
    public function sortLink(string $column): string
    {
        $dir = ($this->sort === $column && $this->dir === 'ASC') ? 'DESC' : 'ASC';
        return '/users?' . http_build_query([
            'q' => $this->search,
            'role' => $this->role,
            'sort' => $column,
            'dir' => $dir,
        ]);
    }

    /** Arrow indicator for the active sort column. */
    public function sortIndicator(string $column): string
    {
        if ($this->sort !== $column) {
            return '';
        }
        return $this->dir === 'ASC' ? ' ▲' : ' ▼';
    }
}