<?php
/**
 * Event-type overview page.
 *
 * @var array{slug:string,name:string,description:?string} $eventType
 * @var \App\Models\EventModel[]                           $events
 *
 * TODO (you): style this to match your Haarlem Jazz design
 * (reuse hero-section / hero-title / hero-subtitle and artist-grid / artist-card
 * from Views/Haarlem/haarlemJazz.php). The hero text now comes from the DB
 * ($eventType), and the cards loop $events — nothing hardcoded.
 */
?>
<section class="events-hero">
    <div class="container">
        <h1 class="events-hero-title"><?= htmlspecialchars($eventType['name']) ?></h1>
        <?php if (!empty($eventType['description'])): ?>
            <p class="events-hero-subtitle"><?= htmlspecialchars($eventType['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="festival-section">
    <?php if (empty($events)): ?>
        <p class="text-center text-muted">No events to show yet.</p>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <a class="artist-card" href="/event/<?= $event->id ?>">
                    <img src="<?= htmlspecialchars($event->image ?? '/assets/images/grote-markt.png') ?>"
                         alt="<?= htmlspecialchars($event->title) ?>">
                    <div class="artist-name"><?= htmlspecialchars($event->title) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>