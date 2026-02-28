<?php
declare(strict_types=1);

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= e($hero['title'] ?? 'Restaurant') ?></title>
    <link rel="stylesheet" href="/assets/css/ratatouille.css">
</head>
<body>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1><?= e($hero['title'] ?? '') ?></h1>
        <p><?= e($hero['subtitle'] ?? '') ?></p>

        <div class="hero-badges">
            <span><?= e($hero['badge_left'] ?? '') ?></span>
            <span><?= e($hero['badge_right'] ?? '') ?></span>
        </div>

        <?php if (!empty($hero['cta'])): ?>
            <a class="btn" href="<?= e($hero['cta']['url'] ?? '#') ?>">
                <?= e($hero['cta']['label'] ?? 'Reserve') ?>
            </a>
        <?php endif; ?>
    </div>
</section>


<!-- ABOUT -->
<section class="about">
    <h2><?= e($about['title'] ?? '') ?></h2>
    <p><?= e($about['text'] ?? '') ?></p>

    <?php if (!empty($about['items'])): ?>
        <div class="about-grid">
            <?php foreach ($about['items'] as $item): ?>
                <div class="about-item">
                    <strong><?= e($item['label'] ?? '') ?></strong>
                    <div><?= e($item['value'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<!-- CHEF -->
<section class="chef">
    <h3><?= e($chef['eyebrow'] ?? '') ?></h3>
    <h2><?= e($chef['name'] ?? '') ?></h2>
    <p><?= e($chef['text'] ?? '') ?></p>

    <?php if (!empty($chef['stats'])): ?>
        <div class="chef-stats">
            <?php foreach ($chef['stats'] as $stat): ?>
                <div class="stat">
                    <strong><?= e($stat['top'] ?? '') ?></strong>
                    <div><?= e($stat['bottom'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<!-- FESTIVAL MENU -->
<section class="menu">
    <h2><?= e($menuHeader['title'] ?? 'Festival Special Menu') ?></h2>

    <div class="menu-grid">
        <?php foreach ($menuCards as $card): ?>
            <div class="menu-card">
                <?php if (!empty($card['image'])): ?>
                    <img src="<?= e($card['image']) ?>" alt="<?= e($card['title'] ?? '') ?>">
                <?php endif; ?>

                <h3><?= e($card['title'] ?? '') ?></h3>
                <p><?= e($card['text'] ?? '') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- GALLERY -->
<section class="gallery">
    <h2><?= e($galleryHdr['title'] ?? 'Gallery') ?></h2>

    <div class="gallery-grid">
        <?php foreach ($gallery as $item): ?>
            <div class="gallery-item">
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['title'] ?? '') ?>">
                <?php endif; ?>
                <div><?= e($item['title'] ?? '') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- FOOTER -->
<footer class="footer">
    <div><?= e($footer['brand'] ?? '') ?></div>
    <div><?= e($footer['description'] ?? '') ?></div>
    <div><?= e($footer['email'] ?? '') ?></div>
    <div><?= e($footer['address'] ?? '') ?></div>
    <div><?= e($footer['copyright'] ?? '') ?></div>
</footer>

</body>
</html>
