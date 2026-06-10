<?php
// Data-driven footer: event links come from the active event types, like the nav.
$footerTypes = (new \App\Services\EventService())->getActiveTypes();
?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 footer-top">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <span class="footer-logo">TF</span>
                    <div>
                        <div class="footer-brand-name">The Festival</div>
                        <div class="footer-brand-sub">Haarlem 2026</div>
                    </div>
                </div>
                <p class="footer-tagline">
                    Six events over four days &mdash; jazz, dance, gourmet, history, stories and a children&rsquo;s
                    adventure, across the heart of Haarlem.
                </p>
            </div>

            <div class="col-6 col-lg-3">
                <h6 class="footer-heading">Events</h6>
                <ul class="footer-links-list">
                    <?php foreach ($footerTypes as $type): ?>
                        <li><a href="/events/<?= htmlspecialchars($type['slug']) ?>"><?= htmlspecialchars($type['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <h6 class="footer-heading">Visit</h6>
                <p class="footer-contact"><i class="bi bi-calendar-event"></i> Week 30 &middot; 23&ndash;26 July 2026</p>
                <p class="footer-contact"><i class="bi bi-geo-alt"></i> Historic Center, Haarlem, NL</p>
                <p class="footer-contact"><i class="bi bi-person-circle"></i> <a href="/account">My account</a></p>
            </div>

            <div class="col-lg-2">
                <h6 class="footer-heading">Follow Us</h6>
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> The Festival &ndash; Haarlem. All rights reserved.</span>
            <span class="footer-links">
                <a href="/privacy">Privacy Policy</a>
                <a href="/register">Create account</a>
                <a href="/cart">Cart</a>
            </span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
