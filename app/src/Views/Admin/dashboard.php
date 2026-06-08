<?php
/**
 * Admin dashboard landing.
 *
 * @var int $eventCount
 * @var int $userCount
 */
?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card admin-stat-card">
            <div class="card-body">
                <div class="admin-stat-number"><?= (int)$eventCount ?></div>
                <div class="admin-stat-label"><i class="bi bi-calendar-event"></i> Events</div>
                <a href="/admin/events" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card admin-stat-card">
            <div class="card-body">
                <div class="admin-stat-number"><?= (int)$userCount ?></div>
                <div class="admin-stat-label"><i class="bi bi-people"></i> Users</div>
                <a href="/users" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>

<p class="mt-4 text-muted">
    Manage festival content from the sidebar. More sections (venues, artists,
    restaurants, homepage content) will appear here as they are built.
</p>
