<?php
/**
 * Order confirmation, shown after a verified Stripe payment.
 *
 * @var \App\Models\OrderModel $order
 *
 * TODO (you): style to taste. PDF tickets + invoice email are the next ticket;
 * for now we confirm the order and list what was bought.
 */
?>
<div class="container my-5">
    <div class="text-center mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size:64px;"></i>
        <h1 class="mt-2">Thank you! Your order is confirmed.</h1>
        <p class="text-muted">
            Invoice <strong><?= htmlspecialchars($order->invoice_number ?? '') ?></strong>
            &middot; <?= htmlspecialchars($order->paid_at ? date('j M Y, H:i', strtotime($order->paid_at)) : '') ?>
        </p>
    </div>

    <div class="card mx-auto" style="max-width:640px;">
        <div class="card-body">
            <h5 class="mb-3">Your tickets</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($order->items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>
                            <?= (int)$item->quantity ?> &times;
                            <?= htmlspecialchars($item->ticket_type_name ?? 'Ticket') ?>
                            <span class="text-muted">— <?= htmlspecialchars($item->event_title ?? '') ?></span>
                            <?php if (!empty($item->special_requests)): ?>
                                <div class="small text-muted"><i class="bi bi-info-circle"></i> Special requests: <?= htmlspecialchars($item->special_requests) ?></div>
                            <?php endif; ?>
                        </span>
                        <span>&euro;<?= number_format($item->lineTotal(), 2) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between fw-bold mt-3">
                <span>Total paid</span><span>&euro;<?= number_format($order->total, 2) ?></span>
            </div>
            <p class="small text-muted mt-3 mb-0">
                Your tickets have been issued. (Emailed PDF tickets and invoice are coming soon.)
            </p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/events/jazz" class="btn btn-outline-secondary">Back to events</a>
    </div>
</div>
