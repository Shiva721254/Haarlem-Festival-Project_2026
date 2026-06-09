<?php
/**
 * Customer order history with pay-later retry actions.
 *
 * @var \App\Models\OrderModel[] $orders
 */
use App\Middleware\AuthMiddleware;
?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">My orders</h1>
            <p class="text-muted mb-0">Pending orders can be paid within 24 hours after checkout.</p>
        </div>
        <a href="/cart" class="btn btn-outline-secondary">
            <i class="bi bi-cart3"></i> Cart
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You do not have any orders yet. <a href="/events/jazz">Browse events</a> to add tickets.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Pay before</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $statusClass = match ($order->status) {
                                'paid' => 'success',
                                'pending' => $order->canPayLater() ? 'warning' : 'secondary',
                                'failed' => 'danger',
                                'cancelled' => 'secondary',
                                default => 'secondary',
                            };
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= (int)$order->id ?></strong>
                                <?php if (!empty($order->invoice_number)): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($order->invoice_number) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $statusClass ?>">
                                    <?= htmlspecialchars(ucfirst($order->status)) ?>
                                </span>
                                <?php if ($order->status === 'pending' && !$order->canPayLater()): ?>
                                    <div class="small text-muted">Payment window expired</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order->created_at ? date('j M Y, H:i', strtotime($order->created_at)) : '-') ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order->pay_later_until ? date('j M Y, H:i', strtotime($order->pay_later_until)) : '-') ?>
                            </td>
                            <td class="text-end">&euro;<?= number_format($order->total, 2) ?></td>
                            <td class="text-end">
                                <?php if ($order->canPayLater()): ?>
                                    <form method="POST" action="/orders/pay" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                        <input type="hidden" name="order_id" value="<?= (int)$order->id ?>">
                                        <button type="submit" class="btn btn-sm btn-purple">
                                            <i class="bi bi-credit-card"></i> Pay now
                                        </button>
                                    </form>
                                <?php elseif ($order->isPaid()): ?>
                                    <a href="/program" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-calendar-heart"></i> Program
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
