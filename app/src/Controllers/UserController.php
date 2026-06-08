<?php  

namespace App\Controllers;
use App\Services\Interfaces\IUserService;
use App\Services\UserService;
use App\Services\RecaptchaService;
use App\ViewModels\ManageUserViewModel;
use App\ViewModels\UsersViewModel;
use App\Models\UserModel;
use App\ViewModels\LoginViewModel;
use App\Middleware\AuthMiddleware;
use App\CustomException\DuplicateEntryException;

class UserController
{
    private IUserService $userService;
    private RecaptchaService $recaptchaService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->recaptchaService = new RecaptchaService();
    }

    public function index()
    {
        AuthMiddleware::requireAdmin(); 
        $users = $this->userService->getAll();
        $vm = new UsersViewModel($users);
        require __DIR__ . "/../Views/Users/index.php";       
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
        require __DIR__ . "/../Views/Users/updateUser.php"; 
    }

    public function displayUser($vars = [])
    {
        $id = $vars['id'] ?? $_SESSION['UserId'];
        AuthMiddleware::requireAdminOrOwner($id);
        $user = $this->userService->getById($id);
        require  __DIR__ . "/../Views/Users/displayUser.php";
    }

    // GET
    public function createUser($vars = [])
    {
        $user = null;           
        $vm = new ManageUserViewModel($user);
        require __DIR__ . "/../Views/Users/createUser.php";       
    }    
    
    // POST
    public function saveUser($vars = [])
    {
        $user = (new UserModel())->fromPost();
        try {
            $token = $_POST['g-recaptcha-response'] ?? null;
            if (!$this->recaptchaService->verify($token)) {
                header('Location: /createUser?error=recaptcha_failed');
                exit();
            }       
            
            if ($user->UserId > 0) {
                $this->userService->update($user);
            } else {
                $this->userService->create($user);
            }

            header('Location: /');
            exit();

        } catch (DuplicateEntryException $e) {
            $error = $e->getMessage();            
            $vm = new ManageUserViewModel($user);
            require __DIR__ . "/../Views/Users/createUser.php";
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
        $email = $_POST['Email'] ?? '';
        $password = $_POST['Password'] ?? '';
        $user = $this->userService->authenticate($email, $password);
        
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
            $vm = new LoginViewModel($email, "Invalid email or password.");
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
