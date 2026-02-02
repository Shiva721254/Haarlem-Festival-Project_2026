<?php
namespace App\Controllers;

class DemoController
{
    // GET: /demo
    public function showDemo()
    {
        require __DIR__ . "/../Views/Demo/demo.php";
    }
}