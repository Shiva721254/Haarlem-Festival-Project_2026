<?php

namespace App\Controllers;

use App\Services\Interfaces\IAccountService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\Redirect;
use App\Middleware\AuthMiddleware;

/**
 * Self-service account management for the logged-in user. The controller only
 * reads the request, delegates to AccountService, flashes and redirects.
 */
class AccountController
{
    private IAccountService $accountService;

    public function __construct(IAccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    // GET: /account
    public function show(): void
    {
        $user = $this->accountService->getById(AuthMiddleware::userId());
        View::render('Account/index', ['user' => $user], 'My Account');
    }

    // POST: /account
    public function update(): void
    {
        $userId = AuthMiddleware::userId();
        $result = $this->accountService->updateFromRequest($userId, $_POST);
        Flash::results($result['messages']);
        if ($result['ok'] && $result['firstName'] !== null) {
            $_SESSION['FirstName'] = $result['firstName'];
        }
        Redirect::to('/account');
    }

    // GET: /account/data — GDPR right of access.
    public function exportData(): void
    {
        $payload = $this->accountService->buildDataExport(AuthMiddleware::userId());
        $this->sendJsonDownload($payload, 'my-haarlem-festival-data.json');
    }

    // POST: /account/delete — GDPR right to erasure.
    public function deleteAccount(): void
    {
        $this->accountService->deleteAccount(AuthMiddleware::userId());
        AuthMiddleware::logout(); // keeps the session so the message survives the redirect
        Flash::success('Your account has been deleted and your personal data removed.');
        Redirect::to('/');
    }

    /** Stream an array as a downloadable JSON file. */
    private function sendJsonDownload(array $payload, string $filename): never
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
