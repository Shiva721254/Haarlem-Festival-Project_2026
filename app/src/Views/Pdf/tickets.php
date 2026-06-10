<?php
/**
 * PDF ticket sheet (rendered to a string and fed to dompdf).
 *
 * @var \App\Models\OrderModel $order
 * @var array<int,array<string,mixed>> $tickets  each with a precomputed 'qr_uri'
 * @var string $customerName
 */
?>
<html><head><style>
    body { font-family: DejaVu Sans, sans-serif; color:#222; }
    h1 { color:#361883; }
    .ticket { border:1px solid #ccc; border-radius:8px; padding:16px; margin-bottom:16px; display:flex; }
    .ticket-info { width:70%; }
    .ticket-qr { width:30%; text-align:right; }
    .ticket-qr img { width:170px; height:170px; }
    .event { font-size:18px; font-weight:bold; }
    .type { color:#5c2379; font-weight:bold; margin-top:4px; }
    .holder { margin-top:6px; font-size:13px; }
    .meta { color:#555; margin-top:6px; font-size:12px; }
    .code { color:#888; margin-top:10px; font-size:10px; }
</style></head><body>
    <h1>Haarlem Festival &mdash; Your tickets</h1>
    <p>Order <?= htmlspecialchars($order->invoice_number ?? (string) $order->id) ?>. Present the QR code at the entrance.</p>
    <?php foreach ($tickets as $t): ?>
        <?php $when = !empty($t['starts_at']) ? date('l j F Y, H:i', strtotime($t['starts_at'])) : ''; ?>
        <div class="ticket">
            <div class="ticket-info">
                <div class="event"><?= htmlspecialchars($t['event_title'] ?? '') ?></div>
                <div class="type"><?= htmlspecialchars($t['ticket_type_name'] ?? '') ?></div>
                <?php if ($customerName !== ''): ?>
                    <div class="holder">Ticket holder: <?= htmlspecialchars($customerName) ?></div>
                <?php endif; ?>
                <div class="meta"><?= htmlspecialchars($when) ?><?= !empty($t['venue_name']) ? ' &middot; ' . htmlspecialchars($t['venue_name']) : '' ?></div>
                <div class="code"><?= htmlspecialchars($t['qr_code']) ?></div>
            </div>
            <div class="ticket-qr"><img src="<?= $t['qr_uri'] ?>" alt="QR"></div>
        </div>
    <?php endforeach; ?>
</body></html>
