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

<section class="container mb-5">
    <div class="events-grid">
        <a class="artist-card" href="/events/yummy">
            <img src="/assets/images/Patronaat.png" alt="Yummy">
            <div class="artist-name">Yummy</div>
        </a>
        <a class="artist-card" href="/events/jazz">
            <img src="/assets/images/gumbo.jpg" alt="Haarlem Jazz">
            <div class="artist-name">Haarlem Jazz</div>
        </a>
        <a class="artist-card" href="/events/dance">
            <img src="/assets/images/grote-markt.png" alt="Dance">
            <div class="artist-name">Dance</div>
        </a>
        <a class="artist-card" href="/events/history">
            <img src="/assets/images/rose.jpg" alt="Historic Haarlem">
            <div class="artist-name">Historic Haarlem</div>
        </a>
    </div>
</section>
