<?php

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        require __DIR__ . '/../Views/Home/index.php';
    }

    public function ratatouille(): void
    {
        require __DIR__ . '/../Views/Restaurants/ratatouille.php';
    }

    public function ml(): void
    {
        require __DIR__ . '/../Views/Restaurants/ml.php';
    }
}
