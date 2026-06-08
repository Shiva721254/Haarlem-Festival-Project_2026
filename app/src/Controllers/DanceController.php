<?php

namespace App\Controllers;

class DanceController
{
    public function index(): void
    {
        require __DIR__ . '/../Views/Dance/index.php';
    }
}
