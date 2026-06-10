<?php
/**
 * Restaurant (Yummy participant) detail page — dynamic, from the database.
 *
 * @var \App\Models\RestaurantModel $restaurant
 * @var array<int,array{id:int,title:string,starts_at:string,ends_at:?string}> $sessions
 */
$hero = $restaurant->image ?: '/assets/images/grote-markt.png';
?>
<section class="container my-5" style="max-width: 960px;">
    <a href="/events/yummy" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Yummy
    </a>

    <div class="row g-4 align-items-center mb-4">
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($hero) ?>" alt="<?= htmlspecialchars($restaurant->name) ?>"
                 class="img-fluid rounded shadow-sm w-100" style="object-fit:cover;aspect-ratio:4/3;">
        </div>
        <div class="col-md-7">
            <h1 class="mb-1"><?= htmlspecialchars($restaurant->name) ?></h1>
            <?php if ($restaurant->stars !== null): ?>
                <div class="mb-2" title="<?= (int)$restaurant->stars ?> stars">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="bi bi-star<?= $s <= $restaurant->stars ? '-fill text-warning' : ' text-muted' ?>"></i>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            <?php if ($restaurant->cuisine): ?>
                <p class="mb-1"><i class="bi bi-egg-fried"></i> <?= htmlspecialchars($restaurant->cuisine) ?></p>
            <?php endif; ?>
            <?php if ($restaurant->address): ?>
                <p class="mb-1 text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant->address) ?></p>
            <?php endif; ?>
            <?php if ($restaurant->price_per_seat !== null): ?>
                <p class="mb-0"><strong>Festival menu:</strong> &euro;<?= number_format($restaurant->price_per_seat, 2) ?> p.p.
                    <span class="text-muted small">(&euro;10 reservation fee paid online)</span></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($restaurant->description)): ?>
        <h4>About</h4>
        <div class="mb-4"><?= $restaurant->description ?></div>
    <?php endif; ?>

    <h4 class="mt-4">Reservation sessions</h4>
    <?php if (empty($sessions)): ?>
        <p class="text-muted">No sessions available yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr><th>Date</th><th>Time</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('l j F Y', strtotime($s['starts_at']))) ?></td>
                            <td>
                                <?= htmlspecialchars(date('H:i', strtotime($s['starts_at']))) ?>
                                <?php if (!empty($s['ends_at'])): ?>&ndash; <?= htmlspecialchars(date('H:i', strtotime($s['ends_at']))) ?><?php endif; ?>
                            </td>
                            <td><a href="/event/<?= (int)$s['id'] ?>" class="btn btn-sm purple-button">Reserve</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
