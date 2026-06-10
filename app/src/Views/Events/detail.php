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
                <!-- Rich HTML authored in the admin WYSIWYG (admin-trusted content). -->
                <div class="event-description"><?= $event->description ?></div>
            <?php endif; ?>

            <?php if (!empty($event->artists)): ?>
                <h4 class="mt-4 mb-3">Line-up</h4>
                <div class="lineup-grid">
                    <?php foreach ($event->artists as $artist): ?>
                        <a class="lineup-card text-decoration-none text-reset" href="/artist/<?= $artist->id ?>" title="View <?= htmlspecialchars($artist->name) ?>">
                            <?php if (!empty($artist->image)): ?>
                                <img src="<?= htmlspecialchars($artist->image) ?>" alt="<?= htmlspecialchars($artist->name) ?>">
                            <?php endif; ?>
                            <div class="lineup-name"><?= htmlspecialchars($artist->name) ?></div>
                            <?php if (!empty($artist->genre)): ?>
                                <div class="lineup-genre"><?= htmlspecialchars($artist->genre) ?></div>
                            <?php endif; ?>
                        </a>
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
                <?php
                    $isReservation = $event->restaurant !== null;
                    $isStories = ($event->event_type_slug ?? null) === 'stories';
                    $isMagic = ($event->event_type_slug ?? null) === 'magic';
                ?>
                <?php if ($isMagic): ?>
                    <?php require __DIR__ . '/partials/magic.php'; ?>
                <?php else: ?>
                <h6 class="mb-3"><?= $isReservation ? 'Reservation' : 'Tickets' ?></h6>

                <?php if (empty($ticketTypes)): ?>
                    <p class="text-muted small">Not on sale yet.</p>
                <?php else: ?>
                    <?php if ($isReservation): ?>
                        <p class="text-muted small">
                            A &euro;10 per-person reservation fee is paid now; the rest of the bill is settled at the restaurant.
                        </p>
                    <?php endif; ?>
                    <?php foreach ($ticketTypes as $ticket): ?>
                        <div class="ticket-option mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold"><?= htmlspecialchars($ticket->name) ?></span>
                                <span><?= $ticket->is_donation ? 'Pay what you like' : '&euro;' . number_format($ticket->price, 2) ?></span>
                            </div>
                            <?php if ($ticket->isSoldOut()): ?>
                                <span class="badge text-bg-secondary mt-1">Sold out</span>
                            <?php else: ?>
                                <form method="POST" action="/cart/add" class="mt-1">
                                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="ticket_type_id" value="<?= $ticket->id ?>">
                                    <input type="hidden" name="return_to" value="/event/<?= $event->id ?>">

                                    <?php if ($ticket->is_donation): ?>
                                        <p class="text-muted small mb-1">
                                            Choose what you'd like to pay — the proceeds support the storytellers' causes.
                                        </p>
                                        <input type="hidden" name="quantity" value="1">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">&euro;</span>
                                            <input type="number" name="amount" value="5.00" min="1" step="0.50"
                                                   class="form-control" aria-label="Donation amount" required>
                                            <button type="submit" class="btn purple-button">Donate &amp; reserve</button>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($isReservation): ?>
                                            <label class="form-label small mb-1">Special requests (allergies, diets, wheelchair…)</label>
                                            <textarea name="special_requests" class="form-control form-control-sm mb-1" rows="2"
                                                      maxlength="500" placeholder="Optional"></textarea>
                                            <p class="text-muted small mb-2">
                                                Optional. Shared only with the venue to handle your request — see our
                                                <a href="/privacy" target="_blank">privacy policy</a>.
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($isStories): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="haarlempas" value="1"
                                                       id="hp-<?= $ticket->id ?>">
                                                <label class="form-check-label small" for="hp-<?= $ticket->id ?>">
                                                    I have a HaarlemPas (25% off)
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                        <div class="d-flex gap-2">
                                            <input type="number" name="quantity" value="1" min="1" max="<?= $ticket->available() ?>"
                                                   class="form-control form-control-sm" style="width:80px;"
                                                   aria-label="<?= $isReservation ? 'Guests' : 'Quantity' ?>">
                                            <button type="submit" class="btn btn-sm purple-button flex-grow-1">
                                                <?= $isReservation ? 'Reserve' : 'Add to cart' ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php endif; /* isMagic */ ?>
            </div>
        </div>

    </div>
</section>
