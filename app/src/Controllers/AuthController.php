<?php 

namespace App\Middleware;
use App\Services\Interfaces\IUserService;
use App\Services\UserService;
use App\Repositories\Interfaces\IUserRepository;
use App\Repositories\UserRepository;
use App\ViewModels\AuthViewModel;

class AuthController
{
    private IUserService $userService;
    private IUserRepository $userRepository;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->userRepository = new UserRepository();
    }

    // GET: /forgot-password
    public function showForgotPassword()
    {
        $vm = new AuthViewModel();
        require __DIR__ . "/../Views/Auth/forgotPassword.php";
    }

    // POST: /send-reset-link
    public function sendResetLink()
    {
        $email = $_POST['Email'] ?? '';
        
        // We always show success to prevent "email fishing"
        $this->userService->sendPasswordReset($email);
        
        header("Location: /showLogin");
        //header("Location: /login?mail_sent=1");
        
        exit();
    }

    // GET: /reset-password (from Email link)
    public function showResetForm()
    {
        $token = $_GET['token'] ?? '';
        
        // Validate token before even showing the form
        $user = $this->userService->validateResetToken($token);

        if (!$user) {
            // Token is garbage or expired; send them back to start
            header("Location: /forgotPassword?error=invalid_token");
            exit();
        }

        require __DIR__ . "/../Views/Auth/resetPassword.php";
    }

    // POST: /update-password
    public function handleResetSubmit()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['Password'] ?? '';
        $confirm = $_POST['PasswordConfirm'] ?? '';

        if ($password !== $confirm) {
            header("Location: /reset-password?token=$token&error=match");
            exit();
        }

        $success = $this->userService->completePasswordReset($token, $password);

        if ($success) {
            header("Location: /showLogin?reset=success");
        } else {
            header("Location: /forgot-password?error=expired");
        }
        exit();
    }

    // --- VERIFY ACCOUNT ---

    // POST: /send-verification-link
    public function sendVerification()
    {
        $userId = $_SESSION['UserId'] ?? null;
        if (!$userId) {
            header("Location: /showLogin");
            exit();
        }
        $user = $this->userService->getById($userId);        

        // We always show success to prevent "email fishing"
        $this->userService->sendVerificationEmail($user->Email);        
        header("Location: /user/" . $userId . "?mail_sent=1");
        
        exit();
    }

    //GET route from the link
    public function verifyAccount()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)){
            header("Location: /showLogin?error=invalid_token");
            exit();
        }
        $success = $this->userService->completeAccountVerification($token);
        if($success){
            header("Location: /displayUser?verified=1");
        } else {
            header("Location: /showLogin?error=expired_token");
        }
        exit();
    }

}