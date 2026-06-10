<?php 

namespace App\Controllers;
use App\Services\Interfaces\IUserService;
use App\Services\UserService;
use App\Repositories\Interfaces\IUserRepository;
use App\Repositories\UserRepository;
use App\ViewModels\AuthViewModel;
use App\Models\UserModel;
use App\Enums\UserRole;
use App\Framework\View;
use App\Framework\Flash;
use App\CustomException\DuplicateEntryException;

class AuthController
{
    private IUserService $userService;
    private IUserRepository $userRepository;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->userRepository = new UserRepository();
    }

    // GET: /register
    public function showRegister(): void
    {
        // Already logged in? No need to register again.
        if (isset($_SESSION['UserId'])) {
            header('Location: /');
            exit();
        }
        View::render('Auth/register', [
            'vm' => new AuthViewModel(),
            'old' => [],
            'captcha' => $this->captchaChallenge(),
        ], 'Create Account');
    }

    // POST: /register
    public function register(): void
    {
        $old = [
            'FirstName' => trim($_POST['FirstName'] ?? ''),
            'LastName'  => trim($_POST['LastName'] ?? ''),
            'Username'  => trim($_POST['Username'] ?? ''),
            'Email'     => trim($_POST['Email'] ?? ''),
        ];
        $password = $_POST['Password'] ?? '';
        $confirm  = $_POST['PasswordConfirm'] ?? '';

        // Server-side validation, independent of any front end checks.
        $error = $this->validateRegistration($old, $password, $confirm);

        // GDPR: an account requires explicit agreement to the privacy policy.
        if ($error === null && empty($_POST['consent'])) {
            $error = 'Please agree to the privacy policy to create an account.';
        }
        if ($error === null && !$this->captchaIsValid($_POST['captcha_answer'] ?? '')) {
            $error = 'Please answer the anti-bot question correctly.';
        }

        if ($error === null) {
            $user = new UserModel();
            $user->Username = $old['Username'];
            $user->FirstName = $old['FirstName'];
            $user->LastName  = $old['LastName'];
            $user->Email     = $old['Email'];
            $user->Password  = $password;
            $user->Role      = UserRole::Customer; // public sign-ups are always customers
            $user->isVerified = false;
            $user->isActive   = true;

            try {
                // Service hashes the password and enforces unique username/email.
                $_POST['PasswordConfirm'] = $confirm; // service re-checks the confirmation
                $this->userService->create($user);

                // Send the account verification email on sign-up.
                $this->userService->sendVerificationEmail($user->Email);

                Flash::success('Account created! Check your email to verify your account, then log in.');
                header('Location: /showLogin');
                exit();
            } catch (DuplicateEntryException $e) {
                $error = 'An account with this username or email already exists.';
            } catch (\Throwable $e) {
                $error = 'Something went wrong creating your account. Please try again.';
            }
        }

        View::render('Auth/register', [
            'vm' => new AuthViewModel($error),
            'old' => $old,
            'captcha' => $this->captchaChallenge(),
        ], 'Create Account');
    }

    /**
     * Validate registration input. Returns an error message, or null if valid.
     *
     * @param array<string,string> $fields
     */
    private function validateRegistration(array $fields, string $password, string $confirm): ?string
    {
        if ($fields['FirstName'] === '' || $fields['LastName'] === '') {
            return 'Please provide your first and last name.';
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $fields['Username'] ?? '')) {
            return 'Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.';
        }
        if (!filter_var($fields['Email'], FILTER_VALIDATE_EMAIL)) {
            return 'Please provide a valid email address.';
        }
        if ($password !== $confirm) {
            return 'Passwords do not match.';
        }
        if (strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[0-9]/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must be at least 8 characters and include a capital letter, a number, and a symbol.';
        }
        return null;
    }

    private function captchaChallenge(): array
    {
        $a = random_int(2, 9);
        $b = random_int(2, 9);
        $_SESSION['registration_captcha'] = $a + $b;
        return ['question' => "$a + $b"];
    }

    private function captchaIsValid(string $answer): bool
    {
        $expected = $_SESSION['registration_captcha'] ?? null;
        unset($_SESSION['registration_captcha']);
        return $expected !== null && (int)$answer === (int)$expected;
    }

    // GET: /forgotPassword
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

        // Always show the same message to avoid leaking which emails exist.
        Flash::success('If that email is registered, a password reset link has been sent.');
        header("Location: /showLogin");
        exit();
    }

    // GET: /resetPassword (from email link)
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
            header("Location: /resetPassword?token=$token&error=match");
            exit();
        }

        $success = $this->userService->completePasswordReset($token, $password);

        if ($success) {
            Flash::success('Your password has been reset. Please log in.');
            header("Location: /showLogin");
        } else {
            Flash::error('That reset link is invalid or has expired. Please request a new one.');
            header("Location: /forgotPassword");
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
            Flash::success('Your account has been verified. You can now log in.');
            header("Location: /showLogin");
        } else {
            Flash::error('That verification link is invalid or has expired.');
            header("Location: /showLogin");
        }
        exit();
    }

}
