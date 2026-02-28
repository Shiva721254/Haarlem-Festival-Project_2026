<?php
declare(strict_types=1);

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= e(($hero['title_lines'][0] ?? 'Restaurant') . ' ' . ($hero['title_accent'] ?? '')) ?></title>
  <link rel="stylesheet" href="/assets/css/Ml.css">
</head>
<body>

<section class="hero" style="background-image: linear-gradient(rgba(0,0,0,.7), rgba(0,0,0,.85)), url('<?= e($hero['image'] ?? '') ?>');">
  <div class="hero-content">
    <div style="letter-spacing:2px; font-size:12px; color:#c9a227; margin-bottom:14px;">
      <?= e($hero['kicker'] ?? '') ?>
    </div>

    <h1>
      <?= e($hero['title_lines'][0] ?? '') ?><br>
      <?= e($hero['title_lines'][1] ?? '') ?><br>
      <span style="color:#c9a227; font-style:italic; font-weight:700;">
        <?= e($hero['title_accent'] ?? '') ?>
      </span>
    </h1>

    <p><?= e($hero['subtitle'] ?? '') ?></p>

    <?php if (!empty($hero['cta'])): ?>
      <a class="btn" href="<?= e($hero['cta']['url'] ?? '#') ?>"><?= e($hero['cta']['label'] ?? 'Reserve') ?></a>
    <?php endif; ?>
  </div>
</section>

<section class="about" style="background:#f3efe8; color:#111;">
  <div style="max-width:1100px; margin:0 auto; display:grid; grid-template-columns: 1.2fr 1fr; gap:50px; align-items:center;">
    <div>
      <div style="letter-spacing:2px; font-size:12px; color:#b38b2f; margin-bottom:10px;">
        <?= e($philosophy['kicker'] ?? '') ?>
      </div>
      <h2 style="color:#111;"><?= e($philosophy['title'] ?? '') ?></h2>
      <p style="color:#333;"><?= e($philosophy['text'] ?? '') ?></p>
    </div>
    <div>
      <?php if (!empty($philosophy['image'])): ?>
        <img src="<?= e($philosophy['image']) ?>" alt="<?= e($philosophy['title'] ?? '') ?>" style="width:100%; border-radius:10px;">
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="menu" style="background:#f3efe8; color:#111;">
  <div style="max-width:1100px; margin:0 auto;">
    <div style="letter-spacing:2px; font-size:12px; color:#b38b2f; margin-bottom:10px;">
      <?= e($featuredHeader['kicker'] ?? '') ?>
    </div>
    <h2 style="color:#111;"><?= e($featuredHeader['title'] ?? '') ?></h2>

    <div class="menu-grid">
      <?php foreach ($featuredCards as $card): ?>
        <div class="menu-card" style="background:#fff; color:#111;">
          <?php if (!empty($card['image'])): ?>
            <img src="<?= e($card['image']) ?>" alt="<?= e($card['title'] ?? '') ?>">
          <?php endif; ?>
          <div style="font-size:12px; letter-spacing:2px; color:#b38b2f; margin-top:10px;">
            <?= e($card['category'] ?? '') ?>
          </div>
          <h3 style="color:#111; margin-top:6px;"><?= e($card['title'] ?? '') ?></h3>
          <p style="color:#333;"><?= e($card['text'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="chef" style="background:#f3efe8; color:#111;">
  <div style="max-width:1100px; margin:0 auto; display:grid; grid-template-columns: 1fr 1.2fr; gap:50px; align-items:center;">
    <div>
      <?php if (!empty($vision['image'])): ?>
        <img src="<?= e($vision['image']) ?>" alt="<?= e($vision['title'] ?? '') ?>" style="width:100%; border-radius:10px;">
      <?php endif; ?>
    </div>
    <div>
      <div style="letter-spacing:2px; font-size:12px; color:#b38b2f; margin-bottom:10px;">
        <?= e($vision['kicker'] ?? '') ?>
      </div>
      <h2 style="color:#111;"><?= e($vision['title'] ?? '') ?></h2>
      <p style="color:#333;"><?= e($vision['text'] ?? '') ?></p>
    </div>
  </div>
</section>

<section class="gallery" style="background:#f3efe8; color:#111;">
  <div style="max-width:1100px; margin:0 auto;">
    <div style="letter-spacing:2px; font-size:12px; color:#b38b2f; margin-bottom:10px;">
      <?= e($experienceHeader['kicker'] ?? '') ?>
    </div>
    <h2 style="color:#111;"><?= e($experienceHeader['title'] ?? '') ?></h2>

    <div class="gallery-grid">
      <?php foreach ($experienceTiles as $t): ?>
        <div class="gallery-item">
          <?php if (!empty($t['image'])): ?>
            <img src="<?= e($t['image']) ?>" alt="<?= e($t['title'] ?? '') ?>">
          <?php endif; ?>
          <div style="color:#111; font-weight:600;"><?= e($t['title'] ?? '') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<footer class="footer">
  <div><?= e($footer['brand'] ?? '') ?></div>
  <div><?= e($footer['description'] ?? '') ?></div>
  <div><?= e($footer['email'] ?? '') ?></div>
  <div><?= e($footer['address'] ?? '') ?></div>
  <div><?= e($footer['copyright'] ?? '') ?></div>
</footer>

</body>
</html>
