<?php
require __DIR__ . '/../vendor/autoload.php';
session_start();
date_default_timezone_set('Europe/Brussels');
/**if (!isset($_SESSION['UserId'])) {
    header('Location: /showLogin');
    exit();
}*/


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

    // product stuff
    $r->addRoute('GET', '/products', ['App\Controllers\ProductController', 'index2']);
    $r->addRoute('GET', '/createProduct', ['App\Controllers\ProductController', 'createProduct']);
    $r->addRoute('GET', '/updateProduct/{id:\d+}', ['App\Controllers\ProductController', 'updateProduct']);
    $r->addRoute('POST', '/saveProduct', ['App\Controllers\ProductController', 'saveProduct']);
    
    $r->addRoute('GET', '/product/{id:\d+}', ['App\Controllers\ProductController', 'displayProduct']);
    $r->addRoute('GET', '/shoppingCart', ['App\Controllers\ProductController', 'shoppingCart']);
    $r->addRoute('POST', '/cart/add', ['App\Controllers\ProductController', 'addProductToShoppingCart']);

    $r->addRoute('GET', '/showCheckout', ['App\Controllers\ProductController', 'showCheckout']);
    $r->addRoute('POST', '/processCheckout', ['App\Controllers\ProductController', 'processCheckout']);
    $r->addRoute('GET', '/orderSuccess', ['App\Controllers\ProductController', 'orderSuccess']);
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
        throw new Exception('Not implemented yet');
        break;
}
