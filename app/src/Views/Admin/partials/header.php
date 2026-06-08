<?php
/**
 * Admin panel layout (top). Rendered by View::renderAdmin around admin views.
 * Provides a Bootstrap sidebar shell distinct from the public site chrome.
 *
 * @var string $title
 */
use App\Middleware\AuthMiddleware;

$adminPath = strtok($_SERVER['REQUEST_URI'] ?? '/admin', '?');
$active = static fn(string $prefix): string => str_starts_with($adminPath, $prefix) ? 'active' : '';
// User management lives on several paths; treat them all as the "Users" section.
$usersActive = preg_match('#^/(users|createUser|updateUser|user|saveUser)#', $adminPath) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> &middot; Haarlem Festival Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-layout">

    <aside class="admin-sidebar">
        <a class="admin-brand" href="/admin">
            <i class="bi bi-stars"></i> Haarlem Admin
        </a>
        <nav class="admin-nav">
            <a class="admin-nav-link <?= $adminPath === '/admin' ? 'active' : '' ?>" href="/admin">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="admin-nav-link <?= $active('/admin/events') ?>" href="/admin/events">
                <i class="bi bi-calendar-event"></i> Events
            </a>
            <a class="admin-nav-link <?= $usersActive ?>" href="/users">
                <i class="bi bi-people"></i> Users
            </a>
        </nav>
        <form method="POST" action="/logout" class="admin-logout">
            <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
            <button type="submit" class="admin-nav-link admin-logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </aside>

    <main class="admin-content">
        <header class="admin-topbar">
            <span class="admin-topbar-title"><?= htmlspecialchars($title ?? 'Admin') ?></span>
            <span class="admin-topbar-user">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['FirstName'] ?? 'Admin') ?>
            </span>
        </header>

        <div class="admin-main">
            <?php foreach (\App\Framework\Flash::pull() as $flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
