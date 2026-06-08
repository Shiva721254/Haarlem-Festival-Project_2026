<?php
require __DIR__ . '/../vendor/autoload.php';
session_start();
date_default_timezone_set('Europe/Brussels');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!App\Middleware\AuthMiddleware::verifyCsrfToken()) {
        http_response_code(403);
        die("CSRF validation failed. Direct access not allowed.");
    }
}

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

$dispatcher = simpleDispatcher(function (RouteCollector $r) {

    // login stuff
    $r->addRoute('GET', '/showLogin', ['App\Controllers\UserController', 'showLogin']);
    $r->addRoute('POST', '/login', ['App\Controllers\UserController', 'login']);
    $r->addRoute('POST', '/logout', ['App\Controllers\UserController', 'logout']);

    // user stuff
    $r->addRoute('GET', '/users', ['App\Controllers\UserController', 'index']);
    $r->addRoute('GET', '/createUser', ['App\Controllers\UserController', 'createUser']);
    $r->addRoute('GET', '/updateUser/{id:\d+}', ['App\Controllers\UserController', 'updateUser']);
    $r->addRoute('POST', '/saveUser', ['App\Controllers\UserController', 'saveUser']);
    $r->addRoute('GET', '/user/{id:\d+}', ['App\Controllers\UserController', 'displayUser']);

    // Public registration
    $r->addRoute('GET', '/register', ['App\Controllers\AuthController', 'showRegister']);
    $r->addRoute('POST', '/register', ['App\Controllers\AuthController', 'register']);

    // Self-service account management
    $r->addRoute('GET', '/account', ['App\Controllers\AccountController', 'show']);
    $r->addRoute('POST', '/account', ['App\Controllers\AccountController', 'update']);

    // Verification Routes
    $r->addRoute('POST', '/send-verification-link', ['App\Controllers\AuthController', 'sendVerification']);
    $r->addRoute('GET', '/verifyAccount', ['App\Controllers\AuthController', 'verifyAccount']);    

    // --- Password Reset Functionality ---    
    // Step 1: Requesting the link
    $r->addRoute('GET', '/forgotPassword', ['App\Controllers\AuthController', 'showForgotPassword']);
    $r->addRoute('POST', '/send-reset-link', ['App\Controllers\AuthController', 'sendResetLink']);

    // Step 2: The actual reset (via email link)
    // Note: The token is usually handled via $_GET['token'] in the controller
    $r->addRoute('GET', '/resetPassword', ['App\Controllers\AuthController', 'showResetForm']);
    $r->addRoute('POST', '/update-password', ['App\Controllers\AuthController', 'handleResetSubmit']);

    $r->addRoute('GET', '/mainJazz', ['App\Controllers\HaarlemController', 'showHaarlemJazz']);
    $r->addRoute('GET', '/dance', ['App\Controllers\DanceController', 'index']);
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'index']);
    $r->addRoute('GET', '/restaurants/ratatouille', ['App\Controllers\HomeController', 'ratatouille']);
    $r->addRoute('GET', '/restaurants/ml', ['App\Controllers\HomeController', 'ml']);

    $r->addRoute('GET', '/admin/edit', ['App\Controllers\AdminContentController', 'edit']);
    $r->addRoute('POST', '/admin/save', ['App\Controllers\AdminContentController', 'save']);
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {

    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo 'Not Found';
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        $class = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $controller = new $class();
        $vars = $routeInfo[2];
        $controller->$method($vars);
        break;
}
