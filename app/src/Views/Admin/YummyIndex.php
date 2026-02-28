<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="/assets/css/home.css">

<!-- HERO -->
<section class="home-hero">
  <div class="home-hero__bg"></div>
  <div class="home-hero__overlay"></div>

  <div class="container home-hero__content">
    <h1 class="home-hero__title"><?= e($hero['title'] ?? '') ?></h1>
    <p class="home-hero__subtitle"><?= e($hero['subtitle'] ?? '') ?></p>

    <div class="home-hero__feature">
      <div class="home-hero__featurebg"></div>
      <div class="home-hero__featureoverlay"></div>

      <div class="home-hero__featurecontent">
        <p class="home-hero__tagline"><?= e($hero['tagline'] ?? '') ?></p>
        <a class="btn btn--gold" href="<?= e($hero['cta']['url'] ?? '#') ?>">
          <?= e($hero['cta']['label'] ?? 'Explore') ?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- EXPERIENCE -->
<section class="home-section">
  <div class="container">
    <h2 class="section-title"><?= e($experience['title'] ?? '') ?></h2>
    <p class="section-subtitle"><?= e($experience['subtitle'] ?? '') ?></p>

    <div class="experience-grid">
      <?php foreach ($experienceCards as $c): ?>
        <article class="card">
          <div class="card__icon">★</div>
          <h3 class="card__title"><?= e($c['title'] ?? '') ?></h3>
          <p class="card__text"><?= e($c['text'] ?? '') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- RESTAURANTS HEADER ONLY (grid comes later from Restaurants MVC) -->
<section class="home-section">
  <div class="container">
    <h2 class="section-title"><?= e($restaurantsHeader['title'] ?? '') ?></h2>
    <p class="section-subtitle"><?= e($restaurantsHeader['subtitle'] ?? '') ?></p>

    <div class="filters">
      <button class="pill is-active">All</button>
      <button class="pill">Dutch</button>
      <button class="pill">French</button>
      <button class="pill">Fish</button>
      <button class="pill">Vegetarian</button>
      <button class="pill">Modern</button>
    </div>

    <div class="restaurants-placeholder">
      <!-- Your restaurants cards grid later -->
    </div>
  </div>
</section>

<!-- PROGRAMS -->
<section class="home-section home-section--alt">
  <div class="container">
    <h2 class="section-title"><?= e($programs['title'] ?? '') ?></h2>
    <p class="section-subtitle"><?= e($programs['subtitle'] ?? '') ?></p>

    <div class="programs-grid">
      <?php foreach ($programTiles as $t): ?>
        <article class="program">
          <div class="program__bg"></div>
          <div class="program__overlay"></div>
          <div class="program__content">
            <h3 class="program__title"><?= e($t['title'] ?? '') ?></h3>
            <p class="program__text"><?= e($t['text'] ?? '') ?></p>
            <a class="btn btn--ghost" href="<?= e($t['cta']['url'] ?? '#') ?>">
              <?= e($t['cta']['label'] ?? 'Explore') ?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
