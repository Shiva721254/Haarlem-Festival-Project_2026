<?php
/**
 * Shopping cart page.
 *
 * @var \App\Models\CartItemModel[] $items
 * @var array{subtotal:float,vat:float,total:float} $totals
 *
 * TODO (you): restyle to match the design. Fully functional as-is.
 */
use App\Middleware\AuthMiddleware;
?>
<div class="container my-5">
    <h1 class="mb-4">Your cart</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="/events/jazz">Browse events</a> to add tickets.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Event</th>
                        <th class="text-end">Price</th>
                        <th style="width:140px;">Qty</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->ticket_type_name) ?></td>
                            <td>
                                <a href="/event/<?= $item->event_id ?>"><?= htmlspecialchars($item->event_title) ?></a>
                            </td>
                            <td class="text-end">&euro;<?= number_format($item->price, 2) ?></td>
                            <td>
                                <form method="POST" action="/cart/update" class="d-flex gap-1">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="ticket_type_id" value="<?= $item->ticket_type_id ?>">
                                    <input type="number" name="quantity" value="<?= $item->quantity ?>" min="0"
                                           max="<?= $item->available ?>" class="form-control form-control-sm" style="width:70px;">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">&euro;<?= number_format($item->lineSubtotal(), 2) ?></td>
                            <td class="text-end">
                                <form method="POST" action="/cart/remove">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="ticket_type_id" value="<?= $item->ticket_type_id ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span><span>&euro;<?= number_format($totals['subtotal'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>incl. VAT</span><span>&euro;<?= number_format($totals['vat'], 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span><span>&euro;<?= number_format($totals['total'], 2) ?></span>
                    </div>
                    <!-- TODO: checkout flow is the next feature -->
                    <button class="btn purple-button w-100 mt-3" disabled>Checkout (coming soon)</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
