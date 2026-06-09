<?php
/**
 * Personal program — the logged-in customer's purchased events, by day.
 *
 * @var \App\Models\ProgramItemModel[] $items
 *
 * TODO (you): restyle to match the design. Fully functional as-is.
 */
// Group items by calendar day for a schedule layout.
$byDay = [];
foreach ($items as $it) {
    $day = date('l j F Y', strtotime($it->starts_at));
    $byDay[$day][] = $it;
}
?>
<div class="container my-5">
    <h1 class="mb-4">My program</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">
            Your program is empty. <a href="/events/jazz">Browse events</a> and buy a ticket to add it here.
        </div>
    <?php else: ?>
        <?php foreach ($byDay as $day => $dayItems): ?>
            <h4 class="mt-4 mb-3 text-purple"><?= htmlspecialchars($day) ?></h4>
            <div class="row g-3">
                <?php foreach ($dayItems as $it): ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($it->title) ?></h5>
                                    <div class="text-muted small mb-2">
                                        <?= htmlspecialchars($it->type_name ?? '') ?>
                                        <?php if (!empty($it->venue_name)): ?>&middot; <?= htmlspecialchars($it->venue_name) ?><?php endif; ?>
                                    </div>
                                    <div class="mb-1">
                                        <i class="bi bi-clock"></i>
                                        <?= htmlspecialchars(date('H:i', strtotime($it->starts_at))) ?>
                                        <?php if (!empty($it->ends_at)): ?>
                                            &ndash; <?= htmlspecialchars(date('H:i', strtotime($it->ends_at))) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small">
                                        <span class="badge text-bg-success"><?= (int)$it->total_tickets ?> ticket(s)</span>
                                        <span class="text-muted"><?= htmlspecialchars($it->ticket_types) ?></span>
                                    </div>
                                </div>
                                <a href="/event/<?= $it->event_id ?>" class="btn btn-sm btn-outline-secondary align-self-start">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
