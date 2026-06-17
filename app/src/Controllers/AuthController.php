<?php

namespace App\Controllers;
use App\Services\Interfaces\IUserService;
use App\ViewModels\AuthViewModel;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Redirect;

class AuthController
{
    private IUserService $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    // GET: /register
    public function showRegister(): void
    {
        // Already logged in? No need to register again.
        if (isset($_SESSION['UserId'])) {
            Redirect::to('/');
        }
        $this->renderRegisterForm();
    }

    // POST: /register
    public function register(): void
    {
        $old = $this->registrationInput();
        $error = $this->registrationGuardError() ?? $this->attemptRegister($old);
        $this->renderRegisterForm($error, $old);
    }

    /** @return array<string,string> */
    private function registrationInput(): array
    {
        return [
            'FirstName' => trim($_POST['FirstName'] ?? ''),
            'LastName'  => trim($_POST['LastName'] ?? ''),
            'Username'  => trim($_POST['Username'] ?? ''),
            'Email'     => trim($_POST['Email'] ?? ''),
        ];
    }

    /** Request-level guards the controller owns: privacy consent and captcha. */
    private function registrationGuardError(): ?string
    {
        if (empty($_POST['consent'])) {
            return 'Please agree to the privacy policy to create an account.';
        }
        $expected = $_SESSION['registration_captcha'] ?? null;
        unset($_SESSION['registration_captcha']);
        if (!$this->userService->verifyRegistrationCaptcha($_POST['captcha_answer'] ?? '', $expected)) {
            return 'Please answer the anti-bot question correctly.';
        }
        return null;
    }

    /** Create the account; redirects on success, otherwise returns the error. */
    private function attemptRegister(array $old): ?string
    {
        $result = $this->userService->registerCustomer($old, $_POST['Password'] ?? '', $_POST['PasswordConfirm'] ?? '');
        if ($result['ok']) {
            Flash::success('Account created! Check your email to verify your account, then log in.');
            Redirect::to('/showLogin');
        }
        return $result['error'];
    }

    private function renderRegisterForm(?string $error = null, array $old = []): void
    {
        $captcha = $this->userService->registrationCaptchaChallenge();
        $_SESSION['registration_captcha'] = $captcha['answer'];
        View::render('Auth/register', [
            'vm' => new AuthViewModel($error),
            'old' => $old,
            'captcha' => ['question' => $captcha['question']],
        ], 'Create Account');
    }

    // GET: /forgotPassword
    public function showForgotPassword()
    {
        $vm = new AuthViewModel();
        View::render('Auth/forgotPassword', ['vm' => $vm], 'Forgot password');
    }

    // POST: /send-reset-link
    public function sendResetLink()
    {
        // Always send (and always show the same message) to avoid leaking which emails exist.
        $this->userService->sendPasswordReset($_POST['Email'] ?? '');
        Flash::success('If that email is registered, a password reset link has been sent.');
        Redirect::to('/showLogin');
    }

    // GET: /resetPassword (from email link)
    public function showResetForm()
    {
        $token = $_GET['token'] ?? '';
        $user = $this->userService->validateResetToken($token);
        if (!$user) {
            // Token is garbage or expired; send them back to start.
            Redirect::to('/forgotPassword?error=invalid_token');
        }
        View::render('Auth/resetPassword', ['user' => $user, 'token' => $token], 'Reset password');
    }

    // POST: /update-password
    public function handleResetSubmit()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['Password'] ?? '';
        if ($password !== ($_POST['PasswordConfirm'] ?? '')) {
            Redirect::to("/resetPassword?token=$token&error=match");
        }
        $this->finishPasswordReset($token, $password);
    }

    private function finishPasswordReset(string $token, string $password): never
    {
        if ($this->userService->completePasswordReset($token, $password)) {
            Flash::success('Your password has been reset. Please log in.');
            Redirect::to('/showLogin');
        }
        Flash::error('That reset link is invalid or has expired. Please request a new one.');
        Redirect::to('/forgotPassword');
    }

    // --- VERIFY ACCOUNT ---

    // POST: /send-verification-link
    public function sendVerification()
    {
        $userId = $_SESSION['UserId'] ?? null;
        if (!$userId) {
            Redirect::to('/showLogin');
        }
        $user = $this->userService->getById($userId);
        $this->userService->sendVerificationEmail($user->Email);
        Redirect::to("/user/$userId?mail_sent=1");
    }

    //GET route from the link
    public function verifyAccount()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            Redirect::to('/showLogin?error=invalid_token');
        }
        $this->flashVerificationOutcome($this->userService->completeAccountVerification($token));
        Redirect::to('/showLogin');
    }

    private function flashVerificationOutcome(bool $ok): void
    {
        $ok
            ? Flash::success('Your account has been verified. You can now log in.')
            : Flash::error('That verification link is invalid or has expired.');
    }
}
