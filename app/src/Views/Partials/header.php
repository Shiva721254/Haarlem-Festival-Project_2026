<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Visit Haarlem' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<nav class="navbar navbar-expand-lg navigation-bar">
    <div class="container-fluid nav-container">
        <a href="/">
            <img class="nav-logo" src="/assets/images/VisitHaarlemLogo.png" alt="VisitHaarlem Logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <?php
            $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
            // Data-driven nav: list the active event types from the database.
            $navTypes = (new \App\Services\EventService())->getActiveTypes();
        ?>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPath === '/' ? 'active-pill' : '' ?>" href="/">Home</a>
                </li>
                <?php foreach ($navTypes as $type): ?>
                    <?php $href = '/events/' . $type['slug']; ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPath === $href ? 'active-pill' : '' ?>" href="<?= htmlspecialchars($href) ?>">
                            <?= htmlspecialchars($type['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?php $cartCount = (new \App\Services\CartService())->itemCount(); ?>
            <a href="/cart" class="btn login-button rounded-circle position-relative" title="Cart">
                <i class="bi bi-cart3"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>
            <?php if (isset($_SESSION['UserId'])): ?>
                <?php
                    $role = $_SESSION['Role'] ?? null;
                    $roleValue = is_object($role) && property_exists($role, 'value') ? $role->value : (is_string($role) ? $role : null);
                ?>
                <?php if (in_array($roleValue, ['admin', 'employee'], true)): ?>
                    <a href="/scanner" class="btn login-button rounded-circle" title="Ticket scanner">
                        <i class="bi bi-qr-code-scan"></i>
                    </a>
                <?php endif; ?>
                <?php if ($roleValue === 'admin'): ?>
                    <a href="/admin" class="btn login-button rounded-circle" title="Admin panel">
                        <i class="bi bi-speedometer2"></i>
                    </a>
                <?php endif; ?>
                <a href="/program" class="btn login-button rounded-circle" title="My program">
                    <i class="bi bi-calendar-heart"></i>
                </a>
                <a href="/account" class="btn login-button rounded-circle" title="Account Information">
                    <i class="bi bi-person-circle"></i>
                    <small class="user-name-label"><?= htmlspecialchars($_SESSION['FirstName'] ?? 'User') ?></small>
                </a>
                <form method="POST" action="/logout" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken() ?>">
                    <button type="submit" class="btn login-button rounded-circle" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            <?php else: ?>
                <a href="/showLogin" class="btn login-button rounded-circle" title="Login">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php $flashes = \App\Framework\Flash::pull(); ?>
<?php if (!empty($flashes)): ?>
    <div class="container mt-3">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
