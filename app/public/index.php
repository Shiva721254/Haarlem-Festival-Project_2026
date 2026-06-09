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
    $r->addRoute('POST', '/deleteUser', ['App\Controllers\UserController', 'deleteUser']);
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

    // Events (DB-driven). {type} is an event_type slug, e.g. jazz, dance, yummy, history.
    $r->addRoute('GET', '/events/{type:[a-z0-9-]+}', ['App\Controllers\EventController', 'index']);
    $r->addRoute('GET', '/event/{id:\d+}', ['App\Controllers\EventController', 'show']);

    // Shopping cart (guests + logged-in users)
    $r->addRoute('GET', '/cart', ['App\Controllers\CartController', 'index']);
    $r->addRoute('POST', '/cart/add', ['App\Controllers\CartController', 'add']);
    $r->addRoute('POST', '/cart/update', ['App\Controllers\CartController', 'update']);
    $r->addRoute('POST', '/cart/remove', ['App\Controllers\CartController', 'remove']);

    // Checkout + Stripe payment (login required)
    $r->addRoute('POST', '/checkout', ['App\Controllers\CheckoutController', 'start']);
    $r->addRoute('GET', '/checkout/success', ['App\Controllers\CheckoutController', 'success']);
    $r->addRoute('GET', '/checkout/cancel', ['App\Controllers\CheckoutController', 'cancel']);

    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'index']);

    $r->addRoute('GET', '/admin/edit', ['App\Controllers\AdminContentController', 'edit']);
    $r->addRoute('POST', '/admin/save', ['App\Controllers\AdminContentController', 'save']);

    // Admin dashboard + event management
    $r->addRoute('GET', '/admin', ['App\Controllers\AdminController', 'dashboard']);
    $r->addRoute('GET', '/admin/events', ['App\Controllers\AdminEventController', 'index']);
    $r->addRoute('GET', '/admin/events/create', ['App\Controllers\AdminEventController', 'create']);
    $r->addRoute('POST', '/admin/events', ['App\Controllers\AdminEventController', 'store']);
    $r->addRoute('GET', '/admin/events/edit/{id:\d+}', ['App\Controllers\AdminEventController', 'edit']);
    $r->addRoute('POST', '/admin/events/update', ['App\Controllers\AdminEventController', 'update']);
    $r->addRoute('POST', '/admin/events/delete', ['App\Controllers\AdminEventController', 'delete']);

    // Admin ticket-type management (scoped to an event)
    $r->addRoute('GET', '/admin/events/{eventId:\d+}/tickets', ['App\Controllers\AdminTicketTypeController', 'index']);
    $r->addRoute('GET', '/admin/events/{eventId:\d+}/tickets/create', ['App\Controllers\AdminTicketTypeController', 'create']);
    $r->addRoute('POST', '/admin/tickets', ['App\Controllers\AdminTicketTypeController', 'store']);
    $r->addRoute('GET', '/admin/tickets/edit/{id:\d+}', ['App\Controllers\AdminTicketTypeController', 'edit']);
    $r->addRoute('POST', '/admin/tickets/update', ['App\Controllers\AdminTicketTypeController', 'update']);
    $r->addRoute('POST', '/admin/tickets/delete', ['App\Controllers\AdminTicketTypeController', 'delete']);
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
