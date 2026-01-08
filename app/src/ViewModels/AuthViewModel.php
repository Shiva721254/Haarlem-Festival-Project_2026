<?php
namespace App\ViewModels;

class AuthViewModel {
    public ?string $error = null;
    public ?string $success = null;

    public function __construct(?string $error = null, ?string $success = null) {
        $this->error = $error;
        $this->success = $success;
    }
}