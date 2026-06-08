<?php

namespace App\Controllers;

class AdminContentController
{
    public function edit(): void
    {
        require __DIR__ . '/../Views/Admin/edit.php';
    }

    public function save(): void
    {
        // TODO: handle save logic
    }
}
