<?php
/**
 * Single event detail page.
 *
 * @var \App\Models\EventModel $event   (with ->venue, ->restaurant, ->artists loaded)
 *
 * Data-driven: title, image, times, description, venue/restaurant and line-up
 * all come from the database. The "Buy tickets" button is a placeholder for the
 * ticketing feature (next ticket).
 */
$heroImage = $event->image ?? '/assets/images/grote-markt.png';
?>
<section class="event-hero" style="background-image:url('<?= htmlspecialchars($heroImage) ?>')">
    <div class="event-hero-overlay">
        <div class="container">
            <a class="event-back" href="/events/<?= htmlspecialchars($event->event_type_slug ?? '') ?>">
                &larr; Back to <?= htmlspecialchars($event->event_type_name ?? 'events') ?>
            </a>
            <h1 class="event-hero-title"><?= htmlspecialchars($event->title) ?></h1>
            <p class="event-hero-meta">
                <i class="bi bi-calendar-event"></i>
                <?= htmlspecialchars(date('l j F Y, H:i', strtotime($event->starts_at))) ?>
                <?php if (!empty($event->ends_at)): ?>
                    &ndash; <?= htmlspecialchars(date('H:i', strtotime($event->ends_at))) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="row g-4">

        <div class="col-lg-8">
            <?php if (!empty($event->description)): ?>
                <!-- plain text for now; switch to raw HTML once the CMS owns this field -->
                <div class="event-description">
                    <p><?= htmlspecialchars($event->description) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->artists)): ?>
                <h4 class="mt-4 mb-3">Line-up</h4>
                <div class="lineup-grid">
                    <?php foreach ($event->artists as $artist): ?>
                        <div class="lineup-card">
                            <?php if (!empty($artist->image)): ?>
                                <img src="<?= htmlspecialchars($artist->image) ?>" alt="<?= htmlspecialchars($artist->name) ?>">
                            <?php endif; ?>
                            <div class="lineup-name"><?= htmlspecialchars($artist->name) ?></div>
                            <?php if (!empty($artist->genre)): ?>
                                <div class="lineup-genre"><?= htmlspecialchars($artist->genre) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="event-info-card">
                <h5 class="mb-3">Details</h5>

                <p class="mb-3">
                    <strong>When</strong><br>
                    <?= htmlspecialchars(date('l j F Y', strtotime($event->starts_at))) ?><br>
                    <?= htmlspecialchars(date('H:i', strtotime($event->starts_at))) ?>
                    <?php if (!empty($event->ends_at)): ?>
                        &ndash; <?= htmlspecialchars(date('H:i', strtotime($event->ends_at))) ?>
                    <?php endif; ?>
                </p>

                <?php if ($event->venue !== null): ?>
                    <p class="mb-3">
                        <strong>Venue</strong><br>
                        <?= htmlspecialchars($event->venue->name) ?>
                        <?php if ($event->venue->address): ?><br><span class="text-muted"><?= htmlspecialchars($event->venue->address) ?></span><?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if ($event->restaurant !== null): ?>
                    <p class="mb-3">
                        <strong>Restaurant</strong><br>
                        <?= htmlspecialchars($event->restaurant->name) ?>
                        <?php if ($event->restaurant->cuisine): ?><br><span class="text-muted"><?= htmlspecialchars($event->restaurant->cuisine) ?></span><?php endif; ?>
                    </p>
                <?php endif; ?>

                <hr>
                <h6 class="mb-3">Tickets</h6>

                <?php if (empty($ticketTypes)): ?>
                    <p class="text-muted small">Tickets are not on sale yet.</p>
                <?php else: ?>
                    <?php foreach ($ticketTypes as $ticket): ?>
                        <div class="ticket-option mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= htmlspecialchars($ticket->name) ?></span>
                                <span>&euro;<?= number_format($ticket->price, 2) ?></span>
                            </div>
                            <?php if ($ticket->isSoldOut()): ?>
                                <span class="badge text-bg-secondary mt-1">Sold out</span>
                            <?php else: ?>
                                <!-- TODO (you): style this add-to-cart row to taste -->
                                <form method="POST" action="/cart/add" class="d-flex gap-2 mt-1">
                                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="ticket_type_id" value="<?= $ticket->id ?>">
                                    <input type="hidden" name="return_to" value="/event/<?= $event->id ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?= $ticket->available() ?>"
                                           class="form-control form-control-sm" style="width:80px;">
                                    <button type="submit" class="btn btn-sm purple-button flex-grow-1">Add to cart</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>
