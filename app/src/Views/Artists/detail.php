<?php
/**
 * Participant (artist) detail page.
 *
 * @var \App\Models\ArtistModel $artist
 * @var array<int,array{id:int,title:string,type_name:string,starts_at:string,ends_at:?string,venue_name:?string}> $schedule
 */
$gallery = $artist->images;
$hero = $artist->image ?: ($gallery[0] ?? '/assets/images/grote-markt.png');
?>
<section class="container my-5" style="max-width: 960px;">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="row g-4 align-items-center mb-4">
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($hero) ?>" alt="<?= htmlspecialchars($artist->name) ?>"
                 class="img-fluid rounded shadow-sm w-100" style="object-fit:cover;aspect-ratio:4/3;">
        </div>
        <div class="col-md-7">
            <h1 class="mb-1"><?= htmlspecialchars($artist->name) ?></h1>
            <?php if ($artist->genre): ?>
                <p class="text-muted mb-3"><?= htmlspecialchars($artist->genre) ?></p>
            <?php endif; ?>
            <?php if ($artist->bio): ?>
                <p class="lead fs-6"><?= htmlspecialchars($artist->bio) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($artist->career_highlights): ?>
        <h4>Career highlights</h4>
        <p><?= nl2br(htmlspecialchars($artist->career_highlights)) ?></p>
    <?php endif; ?>

    <?php if (!empty($gallery)): ?>
        <h4 class="mt-4">Gallery</h4>
        <div class="row g-3 mb-2">
            <?php foreach ($gallery as $img): ?>
                <div class="col-6 col-md-4">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($artist->name) ?>"
                         class="img-fluid rounded w-100" style="object-fit:cover;aspect-ratio:3/2;" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php $tracks = $artist->trackList(); ?>
    <?php if (!empty($tracks) || $artist->audio_url): ?>
        <h4 class="mt-4">Important tracks</h4>
        <?php if (!empty($tracks)): ?>
            <ul>
                <?php foreach ($tracks as $t): ?>
                    <li><?= htmlspecialchars($t) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($artist->audio_url): ?>
            <p class="text-muted small mb-1">Listen to a sample (simulated):</p>
            <audio controls preload="none" class="w-100" style="max-width:480px;">
                <source src="<?= htmlspecialchars($artist->audio_url) ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php endif; ?>
    <?php endif; ?>

    <h4 class="mt-4">Schedule of appearances</h4>
    <?php if (empty($schedule)): ?>
        <p class="text-muted">No scheduled appearances yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr><th>Event</th><th>When</th><th>Where</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $s): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($s['title']) ?>
                                <span class="d-block small text-muted"><?= htmlspecialchars($s['type_name']) ?></span>
                            </td>
                            <td><?= htmlspecialchars(date('D j M, H:i', strtotime($s['starts_at']))) ?></td>
                            <td><?= htmlspecialchars($s['venue_name'] ?? '—') ?></td>
                            <td><a href="/event/<?= (int)$s['id'] ?>" class="btn btn-sm purple-button">Tickets</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
