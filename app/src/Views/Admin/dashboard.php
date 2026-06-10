<?php
/**
 * Admin dashboard landing.
 *
 * @var int $eventCount
 * @var int $ticketTypeCount
 * @var int $orderCount
 * @var int $venueCount
 * @var int $restaurantCount
 * @var int $artistCount
 * @var int $homepageCount
 * @var int $userCount
 */
?>
<?php
$adminSections = [
    [
        'label' => 'Events',
        'description' => 'Create, edit and publish festival sessions for every programme.',
        'icon' => 'bi-calendar-event',
        'href' => '/admin/events',
        'count' => (int)$eventCount,
        'action' => 'Manage events',
    ],
    [
        'label' => 'Tickets',
        'description' => 'Manage ticket types, reservations, pass prices and availability per event.',
        'icon' => 'bi-ticket-perforated',
        'href' => '/admin/events',
        'count' => (int)$ticketTypeCount,
        'action' => 'Open events first',
    ],
    [
        'label' => 'Orders',
        'description' => 'View orders, payment status, customer details and reservation requests.',
        'icon' => 'bi-receipt',
        'href' => '/admin/orders',
        'count' => (int)$orderCount,
        'action' => 'View orders',
    ],
    [
        'label' => 'Venues',
        'description' => 'Maintain event locations used across music, dance, history and stories.',
        'icon' => 'bi-geo-alt',
        'href' => '/admin/venues',
        'count' => (int)$venueCount,
        'action' => 'Manage venues',
    ],
    [
        'label' => 'Restaurants',
        'description' => 'Manage Yummy restaurants, cuisine, seating price, descriptions and images.',
        'icon' => 'bi-shop',
        'href' => '/admin/restaurants',
        'count' => (int)$restaurantCount,
        'action' => 'Manage restaurants',
    ],
    [
        'label' => 'Artists',
        'description' => 'Manage performer profiles, genres, biographies and image galleries.',
        'icon' => 'bi-mic',
        'href' => '/admin/artists',
        'count' => (int)$artistCount,
        'action' => 'Manage artists',
    ],
    [
        'label' => 'Homepage',
        'description' => 'Edit public homepage content blocks and uploaded images.',
        'icon' => 'bi-pencil-square',
        'href' => '/admin/edit',
        'count' => (int)$homepageCount,
        'action' => 'Edit homepage',
    ],
    [
        'label' => 'Users',
        'description' => 'Search, filter and manage customer, employee and administrator accounts.',
        'icon' => 'bi-people',
        'href' => '/users',
        'count' => (int)$userCount,
        'action' => 'Manage users',
    ],
];
?>

<section class="admin-dashboard-hero">
    <div>
        <span class="admin-dashboard-kicker">Festival control panel</span>
        <h1>Admin dashboard</h1>
        <p>Manage content, event entities, tickets, orders and user accounts from one overview.</p>
    </div>
    <a href="/" class="btn btn-outline-secondary" target="_blank" rel="noopener">
        <i class="bi bi-box-arrow-up-right"></i> View website
    </a>
</section>

<div class="admin-dashboard-grid">
    <?php foreach ($adminSections as $section): ?>
        <a class="admin-dashboard-card" href="<?= htmlspecialchars($section['href']) ?>">
            <span class="admin-dashboard-icon">
                <i class="bi <?= htmlspecialchars($section['icon']) ?>"></i>
            </span>
            <span class="admin-dashboard-title-row">
                <span class="admin-dashboard-title"><?= htmlspecialchars($section['label']) ?></span>
                <?php if ($section['count'] !== null): ?>
                    <span class="admin-dashboard-count"><?= (int)$section['count'] ?></span>
                <?php endif; ?>
            </span>
            <span class="admin-dashboard-description"><?= htmlspecialchars($section['description']) ?></span>
            <span class="admin-dashboard-action">
                <?= htmlspecialchars($section['action']) ?> <i class="bi bi-arrow-right"></i>
            </span>
        </a>
    <?php endforeach; ?>
</div>
