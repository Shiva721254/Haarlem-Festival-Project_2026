<?php
/**
 * Admin order detail.
 *
 * @var \App\Models\OrderModel       $order
 * @var \App\Models\UserModel|null   $customer
 */
?>
<div class="container-fluid">
    <a href="/admin/orders" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to orders
    </a>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1">Order #<?= (int)$order->id ?></h2>
            <div class="text-muted"><?= htmlspecialchars($order->invoice_number ?? 'No invoice yet') ?></div>
        </div>
        <span class="badge fs-6 text-bg-<?= $order->status === 'paid' ? 'success' : 'secondary' ?>">
            <?= htmlspecialchars(ucfirst($order->status)) ?>
        </span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Customer</h6>
                    <?php if ($customer !== null): ?>
                        <div><?= htmlspecialchars(trim(($customer->FirstName ?? '') . ' ' . ($customer->LastName ?? ''))) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($customer->Email ?? '') ?></div>
                    <?php else: ?>
                        <div class="text-muted">Unknown</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted">Dates</h6>
                    <div class="small">Created: <?= htmlspecialchars($order->created_at ? date('j M Y, H:i', strtotime($order->created_at)) : '-') ?></div>
                    <div class="small">Paid: <?= htmlspecialchars($order->paid_at ? date('j M Y, H:i', strtotime($order->paid_at)) : '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="card-title mb-3">Items</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Event</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit</th>
                            <th class="text-end">Line</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->items as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item->ticket_type_name ?? 'Ticket') ?>
                                    <?php if (!empty($item->special_requests)): ?>
                                        <div class="alert alert-warning py-1 px-2 mt-1 mb-0 small">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Special request:</strong> <?= htmlspecialchars($item->special_requests) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item->event_title ?? '') ?></td>
                                <td class="text-end"><?= (int)$item->quantity ?></td>
                                <td class="text-end">&euro;<?= number_format($item->unit_price, 2) ?></td>
                                <td class="text-end">&euro;<?= number_format($item->lineTotal(), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end text-muted">Subtotal</td>
                            <td class="text-end">&euro;<?= number_format($order->subtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-muted">VAT</td>
                            <td class="text-end">&euro;<?= number_format($order->vat_total, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total</td>
                            <td class="text-end fw-bold">&euro;<?= number_format($order->total, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
