<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Visit Haarlem' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/header.css">
</head>

<nav class="navbar navbar-expand-lg navigation-bar">
    <div class="container-fluid nav-container">
        <a href="#">
            <img class="nav-logo" src="/assets/images/VisitHaarlemLogo.png" alt="VisitHaarlem Logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Yummy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active-pill" href="/mainJazz">Haarlem Jazz</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Dance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Magical Players</a>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?php if (isset($_SESSION['UserId'])): ?>
                <a href="/user/<?= $_SESSION['UserId'] ?>" class="btn login-button rounded-circle" title="Account Information">
                    <i class="bi bi-person-circle"></i>
                    <small class="user-name-label"><?= htmlspecialchars($_SESSION['FirstName'] ?? 'User') ?></small>
                </a>
            <?php else: ?>
                <a href="/showLogin" class="btn login-button rounded-circle" title="Login">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
            <?php endif; ?>

            <a href="/shoppingCart" class="btn shopping-cart-button rounded-circle position-relative" title="Shopping Cart">
                <i class="bi bi-cart3"></i>
                
            </a>
        </div>
    </div>
</nav>