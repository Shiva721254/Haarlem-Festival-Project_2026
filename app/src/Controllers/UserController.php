<?php  

namespace App\Controllers;
use App\Services\Interfaces\IUserService;
use App\Services\UserService;
use App\ViewModels\ManageUserViewModel;
use App\ViewModels\UsersViewModel;
use App\Models\UserModel;
use App\ViewModels\LoginViewModel;
use App\Middleware\AuthMiddleware;
use App\Framework\View;
use App\Framework\Flash;
use App\CustomException\DuplicateEntryException;

class UserController
{
    private IUserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function index()
    {
        AuthMiddleware::requireAdmin(); 
        $search = trim($_GET['q'] ?? '');
        $role   = trim($_GET['role'] ?? '');
        $sort   = $_GET['sort'] ?? 'LastName';
        $dir    = $_GET['dir'] ?? 'ASC';

        $users = $this->userService->getAll($search, $role, $sort, $dir);
        $vm = new UsersViewModel($users, $search, $role, $sort, $dir);
        View::renderAdmin('Users/index', ['vm' => $vm], 'Users');
    }

    // GET
    public function updateUser($vars = [])
    {
        $id = (int)($vars['id'] ?? 0);
        AuthMiddleware::requireAdminOrOwner($id);        

        if ($id <= 0) {
            header('Location: /users');
            exit();
        }

        $user = $this->userService->getById($id);

        if (!$user) {
            // If user doesn't exist, redirect back to list
            header('Location: /users?error=notfound');
            exit();
        }

        $vm = new ManageUserViewModel($user);
        View::renderAdmin('Users/updateUser', ['vm' => $vm], 'Edit user');
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

        $user = null;
        $vm = new ManageUserViewModel($user);
        View::renderAdmin('Users/createUser', ['vm' => $vm], 'New user');
    }
    
    // POST: /deleteUser
    public function deleteUser()
    {
        AuthMiddleware::requireAdmin();

        $id = (int)($_POST['id'] ?? 0);

        if ($id === (int)($_SESSION['UserId'] ?? 0)) {
            Flash::error('You cannot delete your own account.');
            header('Location: /users');
            exit();
        }

        if ($id > 0) {
            $this->userService->delete($id);
            Flash::success('User deleted.');
        }

        header('Location: /users');
        exit();
    }

    // POST
    public function saveUser($vars = [])
    {
        AuthMiddleware::requireAdmin();

        $user = (new UserModel())->fromPost();
        try {
            if ($user->UserId > 0) {
                $this->userService->update($user);
            } else {
                // Admin-created accounts are trusted: active and pre-verified
                // (no email-confirmation loop needed for staff-created users).
                $user->isActive = true;
                $user->isVerified = true;
                $this->userService->create($user);
            }

            header('Location: /users');
            exit();

        } catch (DuplicateEntryException $e) {
            $error = $e->getMessage();
            $vm = new ManageUserViewModel($user);
            View::renderAdmin('Users/createUser', ['vm' => $vm, 'error' => $error], 'New user');
            exit();
        } catch (\Throwable $e) {
            $vm = new ManageUserViewModel($user);
            View::renderAdmin('Users/createUser', ['vm' => $vm, 'error' => $e->getMessage()], 'New user');
            exit();
        }
    }
    
    //GET
    public function showLogin()
    {
        $vm = new LoginViewModel();
        require __DIR__ . "/../Views/Users/login.php";
    }

    //POST
    public function login()
    {
        $identifier = $_POST['Identifier'] ?? $_POST['Email'] ?? '';
        $password = $_POST['Password'] ?? '';
        $user = $this->userService->authenticate($identifier, $password);
        
        if($user){
            // --- SESSION START ---
            // session_start() is already called in index.php
            session_regenerate_id(true); // Security best practice
            $_SESSION['UserId'] = $user->UserId;
            $_SESSION['Role'] = $user->Role;
            $_SESSION['FirstName'] = $user->FirstName;

            header('Location: /');
            exit();
        } else {
            $vm = new LoginViewModel($identifier, 'Invalid username/email or password.');
            require __DIR__ . "/../Views/Users/login.php";
        }
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        header('Location: /showLogin');
        exit();
    }
}
