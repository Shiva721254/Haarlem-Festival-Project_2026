<?php
namespace App\ViewModels;

class LoginViewModel {
    public string $error = "";
    public string $email = "";

    public function __construct(string $email = "", string $error = "") {
        $this->email = $email;
        $this->error = $error;
    }
}