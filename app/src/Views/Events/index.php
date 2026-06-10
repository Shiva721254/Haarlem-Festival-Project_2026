<?php
/**
 * Event-type overview page.
 *
 * @var array{slug:string,name:string,description:?string} $eventType
 * @var \App\Models\EventModel[]                           $events
 * @var array<int,array{event:\App\Models\EventModel,options:\App\Models\TicketTypeModel[]}> $passes
 */
$passes = $passes ?? [];
use App\Middleware\AuthMiddleware;
?>
<section class="events-hero">
    <div class="container">
        <h1 class="events-hero-title"><?= htmlspecialchars($eventType['name']) ?></h1>
        <?php if (!empty($eventType['description'])): ?>
            <p class="events-hero-subtitle"><?= htmlspecialchars($eventType['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($passes)): ?>
    <section class="festival-section">
        <h3 class="text-center mb-4">Festival passes</h3>
        <div class="row g-3 justify-content-center">
            <?php foreach ($passes as $pass): ?>
                <?php foreach ($pass['options'] as $opt): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 text-center">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($opt->name) ?></h5>
                                <p class="text-muted small flex-grow-1"><?= htmlspecialchars($pass['event']->description ?? '') ?></p>
                                <div class="fw-bold mb-2">&euro;<?= number_format($opt->price, 2) ?></div>
                                <?php if ($opt->isSoldOut()): ?>
                                    <span class="badge text-bg-secondary">Sold out</span>
                                <?php else: ?>
                                    <form method="POST" action="/cart/add">
                                        <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                        <input type="hidden" name="ticket_type_id" value="<?= $opt->id ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="return_to" value="/events/<?= htmlspecialchars($eventType['slug']) ?>">
                                        <button type="submit" class="btn btn-sm purple-button w-100">Add pass to cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="festival-section">
    <?php if (empty($events)): ?>
        <p class="text-center text-muted">No events to show yet.</p>
    <?php else: ?>
        <div class="lineup-section">
            <div class="lineup-section-header">
                <h2>Festival Lineup</h2>
                <p>Reserve your place for the Haarlem Festival <?= htmlspecialchars(strtolower($eventType['name'])) ?> sessions.</p>
            </div>

            <div class="event-lineup-grid">
                <?php $availability = $availability ?? []; ?>
                <?php foreach ($events as $event): ?>
                    <?php
                        $avail = $availability[$event->id] ?? null;
                        $starts = strtotime($event->starts_at);
                        $day = strtoupper(date('M', $starts));
                        $date = date('d', $starts);
                        $time = date('H:i', $starts);
                    ?>
                    <article class="event-lineup-card">
                        <a class="event-lineup-image" href="/event/<?= $event->id ?>">
                            <?php if ($avail === 0): ?>
                                <span class="event-status sold">Sold out</span>
                            <?php elseif ($avail !== null && $avail <= 20): ?>
                                <span class="event-status low">Only <?= (int)$avail ?> left</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($event->image ?? '/assets/images/grote-markt.png') ?>"
                                 alt="<?= htmlspecialchars($event->title) ?>">
                            <div class="event-date-badge">
                                <span><?= htmlspecialchars($day) ?></span>
                                <strong><?= htmlspecialchars($date) ?></strong>
                                <em><i class="bi bi-clock"></i> <?= htmlspecialchars($time) ?></em>
                            </div>
                        </a>

                        <div class="event-lineup-body">
                            <div class="event-location">
                                <i class="bi bi-geo-alt"></i>
                                <?= htmlspecialchars($event->venue->name ?? 'Haarlem Festival venue') ?>
                            </div>
                            <h3>
                                <a href="/event/<?= $event->id ?>"><?= htmlspecialchars($event->title) ?></a>
                            </h3>
                            <?php if (!empty($event->description)): ?>
                                <p><?= htmlspecialchars(mb_strimwidth(strip_tags($event->description), 0, 145, '...')) ?></p>
                            <?php endif; ?>
                            <a href="/event/<?= $event->id ?>" class="btn event-reserve-button">
                                <?= $avail === 0 ? 'View details' : 'Reserve Ticket' ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
