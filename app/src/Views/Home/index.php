<?php
/**
 * Database-driven homepage.
 *
 * @var array<string,\App\Models\ContentBlockModel> $blocks
 */
$hero = $blocks['hero'];
$intro = $blocks['intro'];
$practical = $blocks['practical'];
$heroImage = $hero->image_path ?: '/assets/images/haarlem-homepage-hero.jpeg';
?>
<section class="home-hero" style="background-image:url('<?= htmlspecialchars($heroImage) ?>')">
    <div class="home-hero-overlay">
        <div class="container">
            <div class="home-hero-copy">
                <?= $hero->html ?>
                <div class="home-hero-actions">
                    <a href="/events/jazz" class="btn purple-button">Browse events</a>
                    <a href="/register" class="btn btn-light">Create account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-7">
            <div class="home-content-block">
                <?= $intro->html ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="home-content-block home-content-accent">
                <?= $practical->html ?>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="home-info-header text-center">
        <h2>Festival information</h2>
        <p>Plan your visit, manage tickets, and know what to expect during Haarlem Festival.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="home-info-card">
                <i class="bi bi-ticket-perforated"></i>
                <h3>Tickets and passes</h3>
                <p>Buy tickets for individual events or choose available day and all-access passes for selected festival programmes.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="home-info-card">
                <i class="bi bi-calendar-heart"></i>
                <h3>Personal program</h3>
                <p>After payment, your tickets are added to your personal program so you can view your festival schedule.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="home-info-card">
                <i class="bi bi-credit-card"></i>
                <h3>Payment options</h3>
                <p>Pay online with iDEAL or card. If payment is interrupted, pending orders can still be paid within 24 hours.</p>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="home-info-card">
                <i class="bi bi-qr-code-scan"></i>
                <h3>At the entrance</h3>
                <p>Bring your ticket QR code. Festival staff scan tickets at the entrance and will warn if a ticket was already used.</p>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="home-pass-panel">
        <div class="home-pass-copy">
            <span class="home-pass-label">Festival passes</span>
            <h2>Choose a pass when you want more than one session</h2>
            <p>
                Some festival programmes offer day passes or all-access passes. A day pass gives access to
                selected sessions on one festival day, while an all-access pass gives access to all sessions
                of that programme during the full festival period, subject to venue capacity.
            </p>
        </div>
        <div class="home-pass-options">
            <?php foreach (($passes ?? []) as $typeName => $options): ?>
                <div class="home-pass-option">
                    <h3><?= htmlspecialchars($typeName) ?></h3>
                    <ul class="list-unstyled mb-2 small">
                        <?php foreach ($options as $opt): ?>
                            <li class="d-flex justify-content-between">
                                <span><?= htmlspecialchars($opt['option_name']) ?></span>
                                <span class="fw-semibold ms-3">&euro;<?= number_format((float) $opt['price'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="/events/<?= htmlspecialchars($options[0]['slug']) ?>" class="small">View <?= htmlspecialchars($typeName) ?> passes &rarr;</a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($passes)): ?>
                <p class="text-muted">Passes are not on sale yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="home-info-header text-center">
        <h2>The events</h2>
        <p>Four days, six events — Thursday 23 to Sunday 26 July 2026 (week 30). Tap any event for the full programme.</p>
    </div>

    <?php
    /** Price label per event type (handles museum-only / reservation / donation). */
    $priceLabel = static function (array $s): string {
        $from = $s['from_price'];
        if ($s['slug'] === 'magic') {
            return 'Free &middot; tickets at the museum';
        }
        if ($from === null) {
            return 'See programme';
        }
        $price = '&euro;' . number_format((float) $from, 2);
        if ($s['slug'] === 'yummy') {
            return $price . ' p.p. reservation';
        }
        if ($s['slug'] === 'stories') {
            return 'from ' . $price . ' &middot; or pay what you like';
        }
        return 'from ' . $price;
    };
    ?>

    <div class="row g-4">
        <?php foreach (($summaries ?? []) as $s): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h3 class="h5 mb-0"><?= htmlspecialchars($s['name']) ?></h3>
                            <span class="badge text-bg-light border ms-2"><?= $priceLabel($s) ?></span>
                        </div>
                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars($s['description'] ?? '') ?></p>
                        <a href="/events/<?= htmlspecialchars($s['slug']) ?>" class="btn btn-sm purple-button align-self-start">
                            View programme &rarr;
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($schedule)): ?>
<section class="container my-5">
    <div class="home-info-header text-center">
        <h2>Festival schedule</h2>
        <p>A day-by-day overview of what&rsquo;s on during the festival.</p>
    </div>
    <div class="row g-3">
        <?php foreach ($schedule as $day => $rows): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-header text-center fw-semibold">
                        <?= htmlspecialchars(date('l', strtotime($day))) ?>
                        <span class="d-block small text-muted"><?= htmlspecialchars(date('j M Y', strtotime($day))) ?></span>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($rows as $r): ?>
                            <?php $from = substr($r['first_t'], 0, 5); $to = substr($r['last_t'], 0, 5); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="/events/<?= htmlspecialchars($r['slug']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($r['type_name']) ?>
                                </a>
                                <span class="small text-muted">
                                    <?= $from === $to ? htmlspecialchars($from) : htmlspecialchars($from) . '&ndash;' . htmlspecialchars($to) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="container my-5">
    <div class="home-info-header text-center">
        <h2>Where it happens</h2>
        <p>The festival takes place across Haarlem and nearby Bloemendaal &amp; Overveen. Walking tours start at the St. Bavo Church on the Grote Markt.</p>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ratio ratio-16x9 rounded overflow-hidden border">
                <iframe
                    title="Festival locations in Haarlem"
                    src="https://www.openstreetmap.org/export/embed.html?bbox=4.585%2C52.370%2C4.665%2C52.412&amp;layer=mapnik&amp;marker=52.3814%2C4.6376"
                    style="border:0;" loading="lazy"></iframe>
            </div>
            <a class="small" href="https://www.openstreetmap.org/?mlat=52.3814&amp;mlon=4.6376#map=14/52.3850/4.6300" target="_blank" rel="noopener">
                View a larger map &rarr;
            </a>
        </div>
        <div class="col-lg-5">
            <h3 class="h5 mb-3">Festival locations</h3>
            <ul class="list-group list-group-flush">
                <?php foreach (($locations ?? []) as $loc): ?>
                    <li class="list-group-item px-0">
                        <span class="fw-semibold"><?= htmlspecialchars($loc['name']) ?></span>
                        <?php if (!empty($loc['address'])): ?>
                            <span class="d-block small text-muted"><?= htmlspecialchars($loc['address']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
