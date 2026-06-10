<?php
/**
 * Magic@Teylers panel (rendered on the event detail page for the kids event).
 * The app is the companion, not the event — tickets are bought at the museum.
 *
 * @var \App\Models\EventModel $event
 */
?>
<div class="alert alert-info">
    <strong>A special event for children (ages 8–12).</strong>
    Download the companion app and buy your ticket at Teylers Museum — there is
    <strong>no additional online cost</strong> for Magic@Teylers.
</div>

<div class="d-grid gap-2 d-sm-flex mb-4">
    <a href="#download-app" class="btn purple-button">
        <i class="bi bi-phone"></i> Download the app
    </a>
    <span class="align-self-center text-muted small">Available for iOS and Android.</span>
</div>

<h6 class="mb-2">The Secret of Professor Teyler — six challenges</h6>
<p class="text-muted small">
    Enter the code from each location in the app to unlock clues, collect facts
    and solve the mystery. You are guided step by step.
</p>
<ol class="small mb-4">
    <li><strong>Teyler’s Secret</strong> — the backstory; find a code to reveal the location of Professor Teyler’s notebook.</li>
    <li><strong>The Egg Problem</strong> — use science to find the two fresh eggs out of six.</li>
    <li><strong>The Lost Calculator</strong> — solve a maths puzzle by combining the right numbers.</li>
    <li><strong>The Broken Circuit</strong> — repair the circuit to make two lamps glow.</li>
    <li><strong>The Scale Problem</strong> — find the right amount of water to tip the scale.</li>
    <li><strong>The Final Enigma</strong> — answer questions about the museum to earn the final clue.</li>
</ol>

<h6 class="mb-2">Also at Teylers: The Lorentz Formula</h6>
<p class="text-muted small mb-1">
    A guided theatre tour for the whole family (ages 10+) in the Lorentz Lab,
    on Friday, Saturday and Sunday at 12:30, 14:00 and 15:00. Register at the
    museum — there is room for 20 people per session.
</p>
<a class="small" href="https://teylersmuseum.nl/nl/zien-en-doen/de-lorentz-formule" target="_blank" rel="noopener">
    More about The Lorentz Formula →
</a>

<div id="download-app" class="card bg-light border-0 mt-4">
    <div class="card-body text-center">
        <h6 class="mb-2"><i class="bi bi-stars"></i> Get the Magic@Teylers app</h6>
        <p class="text-muted small mb-3">Bring the mystery to life — before, during and after your visit.</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="#" class="btn btn-dark btn-sm"><i class="bi bi-apple"></i> App Store</a>
            <a href="#" class="btn btn-dark btn-sm"><i class="bi bi-google-play"></i> Google Play</a>
        </div>
    </div>
</div>
