<?php /** Privacy policy — plain, customer-facing summary of how data is used. */ ?>
<section class="container py-5" style="max-width: 820px;">
    <h1 class="mb-4">Privacy policy</h1>
    <p class="text-muted">Last updated: <?= date('j F Y') ?>. This page explains what personal
        data the Haarlem Festival website collects, why, and the choices you have.</p>

    <h4 class="mt-4">What we collect and why</h4>
    <ul>
        <li><strong>Account details</strong> (name, email, password) — to create your account, sign you in, and contact you about your orders. <em>Lawful basis: contract.</em></li>
        <li><strong>Orders &amp; tickets</strong> (events, amounts, invoices, payment reference) — to sell and deliver your tickets and meet bookkeeping obligations. <em>Lawful basis: contract / legal obligation.</em></li>
        <li><strong>Reservation special requests</strong> (e.g. allergies, dietary or accessibility needs) — shared <strong>only</strong> with the restaurant or venue you booked, solely to handle your request. Providing this is optional. <em>Lawful basis: your explicit consent.</em></li>
        <li><strong>Profile picture</strong> (optional) — only if you upload one.</li>
    </ul>
    <p>We do not sell your data, and we do not use it for advertising. Payment card details are
        handled by our payment provider (Stripe) and never stored on our servers.</p>

    <h4 class="mt-4">How long we keep it</h4>
    <p>Account data is kept while your account is active. Order and invoice records are kept for the
        period required by law. When you delete your account, your personal details are removed and
        any retained transaction records are anonymised.</p>

    <h4 class="mt-4">Your rights</h4>
    <ul>
        <li><strong>Access / portability</strong> — download a copy of your data from your account page.</li>
        <li><strong>Erasure</strong> — delete your account from your account page; we remove your personal details.</li>
        <li><strong>Rectification</strong> — edit your name and email on your account page.</li>
    </ul>
    <p>If you’re logged in, manage these from <a href="/account">your account</a>.</p>

    <h4 class="mt-4">Security</h4>
    <p>Passwords are stored hashed, forms are protected against cross-site request forgery, and the
        session cookie is restricted (HttpOnly, SameSite). Access to admin and staff features is
        role-restricted.</p>
</section>
