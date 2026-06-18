<?php

namespace App\Controllers;
use App\Services\Interfaces\IUserService;
use App\ViewModels\ManageUserViewModel;
use App\ViewModels\UsersViewModel;
use App\Models\UserModel;
use App\ViewModels\LoginViewModel;
use App\Middleware\AuthMiddleware;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Redirect;
use App\CustomException\DuplicateEntryException;

class UserController
{
    private IUserService $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        AuthMiddleware::requireAdmin();
        [$search, $role, $sort, $dir] = $this->listParams();
        $users = $this->userService->getAll($search, $role, $sort, $dir);
        $vm = new UsersViewModel($users, $search, $role, $sort, $dir);
        View::renderAdmin('Users/index', ['vm' => $vm], 'Users');
    }

    /** @return array{0:string,1:string,2:string,3:string} */
    private function listParams(): array
    {
        return [trim($_GET['q'] ?? ''), trim($_GET['role'] ?? ''),
                $_GET['sort'] ?? 'LastName', $_GET['dir'] ?? 'ASC'];
    }

    // GET
    public function updateUser($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);
        AuthMiddleware::requireAdminOrOwner($id);
        $user = $this->requireUser($id);
        $vm = new ManageUserViewModel($user);
        View::renderAdmin('Users/updateUser', ['vm' => $vm], 'Edit user');
    }

    /** Load a user by id or redirect back to the list. */
    private function requireUser(int $id): UserModel
    {
        if ($id <= 0) {
            Redirect::to('/users');
        }
        $user = $this->userService->getById($id);
        if (!$user) {
            Redirect::to('/users?error=notfound');
        }
        return $user;
    }

    public function displayUser($vars = [])
    {
        $id = $vars['id'] ?? $_SESSION['UserId'];
        AuthMiddleware::requireAdminOrOwner($id);
        $user = $this->userService->getById($id);
        View::renderAdmin('Users/displayUser', ['user' => $user], 'User');
    }

    // GET
    public function createUser($vars = [])
    {
        AuthMiddleware::requireAdmin();
        $vm = new ManageUserViewModel(null);
        View::renderAdmin('Users/createUser', ['vm' => $vm], 'New user');
    }

    // POST: /deleteUser
    public function deleteUser()
    {
        AuthMiddleware::requireAdmin();
        $this->deleteUserById((int)($_POST['id'] ?? 0));
        Redirect::to('/users');
    }

    private function deleteUserById(int $id): void
    {
        if ($id === (int)($_SESSION['UserId'] ?? 0)) {
            Flash::error('You cannot delete your own account.');
            return;
        }
        if ($id > 0) {
            $this->userService->delete($id);
            Flash::success('User deleted.');
        }
    }

    // POST
    public function saveUser($vars = [])
    {
        AuthMiddleware::requireAdmin();
        $result = $this->userService->saveAdminUser($_POST);
        if (!$result['ok']) {
            $this->renderUserError($result['user'], $result['error']);
        }
        Redirect::to('/users');
    }

    private function renderUserError(UserModel $user, string $error): never
    {
        $vm = new ManageUserViewModel($user);
        View::renderAdmin('Users/createUser', ['vm' => $vm, 'error' => $error], 'New user');
        exit();
    }

    //GET
    public function showLogin()
    {
        $vm = new LoginViewModel();
        View::render('Users/login', ['vm' => $vm], 'Login');
    }

    //POST
    public function login()
    {
        $identifier = $_POST['Identifier'] ?? $_POST['Email'] ?? '';
        $user = $this->userService->authenticate($identifier, $_POST['Password'] ?? '');
        if (!$user) {
            $vm = new LoginViewModel($identifier, 'Invalid username/email or password.');
            View::render('Users/login', ['vm' => $vm], 'Login');
            return;
        }
        $this->startSession($user);
        Redirect::to('/');
    }

    private function startSession(UserModel $user): void
    {
        session_regenerate_id(true); // Security best practice
        $_SESSION['UserId'] = $user->UserId;
        $_SESSION['Role'] = $user->Role->value;
        $_SESSION['FirstName'] = $user->FirstName;
    }

    public function logout()
    {
        $_SESSION = [];
        $this->clearSessionCookie();
        session_destroy();
        Redirect::to('/showLogin');
    }

    private function clearSessionCookie(): void
    {
        if (!ini_get("session.use_cookies")) {
            return;
        }
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
}
