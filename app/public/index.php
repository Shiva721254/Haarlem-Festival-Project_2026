<?php
require __DIR__ . '/../vendor/autoload.php';
// Harden the session cookie: not readable from JS (XSS), sent same-site only
// (CSRF defence in depth), and Secure when served over HTTPS.
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off'),
]);
session_start();
date_default_timezone_set('Europe/Brussels');
// The Stripe webhook is a server-to-server POST authenticated by its own
// signature, so it is exempt from the session CSRF check.
$requestPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath !== '/webhook/stripe') {
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
    // GDPR rights: data access (export) and erasure (delete account)
    $r->addRoute('GET', '/account/data', ['App\Controllers\AccountController', 'exportData']);
    $r->addRoute('POST', '/account/delete', ['App\Controllers\AccountController', 'deleteAccount']);

    // Privacy policy
    $r->addRoute('GET', '/privacy', ['App\Controllers\HomeController', 'privacy']);

    // Personal program (a customer's purchased events)
    $r->addRoute('GET', '/program', ['App\Controllers\ProgramController', 'index']);

    // Customer order history and pay-later retry
    $r->addRoute('GET', '/orders', ['App\Controllers\CustomerOrderController', 'index']);
    $r->addRoute('POST', '/orders/pay', ['App\Controllers\CustomerOrderController', 'pay']);

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

    // Participant (artist) detail page
    $r->addRoute('GET', '/artist/{id:\d+}', ['App\Controllers\ArtistController', 'show']);

    // Shopping cart (guests + logged-in users)
    $r->addRoute('GET', '/cart', ['App\Controllers\CartController', 'index']);
    $r->addRoute('POST', '/cart/add', ['App\Controllers\CartController', 'add']);
    $r->addRoute('POST', '/cart/update', ['App\Controllers\CartController', 'update']);
    $r->addRoute('POST', '/cart/remove', ['App\Controllers\CartController', 'remove']);

    // Ticket scanner (employee + admin)
    $r->addRoute('GET', '/scanner', ['App\Controllers\ScannerController', 'index']);
    $r->addRoute('POST', '/scanner/scan', ['App\Controllers\ScannerController', 'scan']);

    // Checkout + Stripe payment (login required)
    $r->addRoute('POST', '/checkout', ['App\Controllers\CheckoutController', 'start']);
    $r->addRoute('GET', '/checkout/success', ['App\Controllers\CheckoutController', 'success']);
    $r->addRoute('GET', '/checkout/cancel', ['App\Controllers\CheckoutController', 'cancel']);

    // Stripe webhook (server-to-server; signature-verified, CSRF-exempt)
    $r->addRoute('POST', '/webhook/stripe', ['App\Controllers\WebhookController', 'stripe']);

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

    // Admin order management
    $r->addRoute('GET', '/admin/orders', ['App\Controllers\AdminOrderController', 'index']);
    $r->addRoute('GET', '/admin/orders/export', ['App\Controllers\AdminOrderController', 'export']);
    $r->addRoute('GET', '/admin/orders/{id:\d+}', ['App\Controllers\AdminOrderController', 'show']);

    // Admin ticket-type management (scoped to an event)
    $r->addRoute('GET', '/admin/events/{eventId:\d+}/tickets', ['App\Controllers\AdminTicketTypeController', 'index']);
    $r->addRoute('GET', '/admin/events/{eventId:\d+}/tickets/create', ['App\Controllers\AdminTicketTypeController', 'create']);
    $r->addRoute('POST', '/admin/tickets', ['App\Controllers\AdminTicketTypeController', 'store']);
    $r->addRoute('GET', '/admin/tickets/edit/{id:\d+}', ['App\Controllers\AdminTicketTypeController', 'edit']);
    $r->addRoute('POST', '/admin/tickets/update', ['App\Controllers\AdminTicketTypeController', 'update']);
    $r->addRoute('POST', '/admin/tickets/delete', ['App\Controllers\AdminTicketTypeController', 'delete']);

    // Admin venue management
    $r->addRoute('GET', '/admin/venues', ['App\Controllers\AdminVenueController', 'index']);
    $r->addRoute('GET', '/admin/venues/create', ['App\Controllers\AdminVenueController', 'create']);
    $r->addRoute('POST', '/admin/venues', ['App\Controllers\AdminVenueController', 'store']);
    $r->addRoute('GET', '/admin/venues/edit/{id:\d+}', ['App\Controllers\AdminVenueController', 'edit']);
    $r->addRoute('POST', '/admin/venues/update', ['App\Controllers\AdminVenueController', 'update']);
    $r->addRoute('POST', '/admin/venues/delete', ['App\Controllers\AdminVenueController', 'delete']);

    // Admin restaurant management
    $r->addRoute('GET', '/admin/restaurants', ['App\Controllers\AdminRestaurantController', 'index']);
    $r->addRoute('GET', '/admin/restaurants/create', ['App\Controllers\AdminRestaurantController', 'create']);
    $r->addRoute('POST', '/admin/restaurants', ['App\Controllers\AdminRestaurantController', 'store']);
    $r->addRoute('GET', '/admin/restaurants/edit/{id:\d+}', ['App\Controllers\AdminRestaurantController', 'edit']);
    $r->addRoute('POST', '/admin/restaurants/update', ['App\Controllers\AdminRestaurantController', 'update']);
    $r->addRoute('POST', '/admin/restaurants/delete', ['App\Controllers\AdminRestaurantController', 'delete']);

    // Admin artist management
    $r->addRoute('GET', '/admin/artists', ['App\Controllers\AdminArtistController', 'index']);
    $r->addRoute('GET', '/admin/artists/create', ['App\Controllers\AdminArtistController', 'create']);
    $r->addRoute('POST', '/admin/artists', ['App\Controllers\AdminArtistController', 'store']);
    $r->addRoute('GET', '/admin/artists/edit/{id:\d+}', ['App\Controllers\AdminArtistController', 'edit']);
    $r->addRoute('POST', '/admin/artists/update', ['App\Controllers\AdminArtistController', 'update']);
    $r->addRoute('POST', '/admin/artists/delete', ['App\Controllers\AdminArtistController', 'delete']);
    $r->addRoute('POST', '/admin/artists/images/delete', ['App\Controllers\AdminArtistController', 'deleteImage']);
    $r->addRoute('POST', '/admin/artists/{id:\d+}/images', ['App\Controllers\AdminArtistController', 'uploadImage']);
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
