<?php
/**
 * @var \App\Models\OrderModel[] $orders
 * @var string|null $status
 * @var string[] $statuses
 * @var array<string,string> $exportColumns
 */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Orders</h4>
</div>

<form method="GET" action="/admin/orders" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All orders</option>
                <?php foreach ($statuses as $option): ?>
                    <option value="<?= htmlspecialchars($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                        <?= ucfirst($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </div>
</form>

<form method="GET" action="/admin/orders/export" class="card card-body mb-3">
    <?php if ($status !== null): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <strong>Export orders</strong>
        <button type="submit" class="btn btn-purple btn-sm">
            <i class="bi bi-download"></i> Export CSV
        </button>
    </div>
    <div class="row g-2">
        <?php foreach ($exportColumns as $key => $label): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="columns[]" id="col-<?= htmlspecialchars($key) ?>"
                           value="<?= htmlspecialchars($key) ?>" checked>
                    <label class="form-check-label" for="col-<?= htmlspecialchars($key) ?>">
                        <?= htmlspecialchars($label) ?>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</form>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th class="text-end">Items</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">VAT</th>
                    <th class="text-end">Total</th>
                    <th>Created</th>
                    <th>Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a href="/admin/orders/<?= (int)$order->id ?>"><strong>#<?= (int)$order->id ?></strong></a><br>
                            <span class="text-muted small"><?= htmlspecialchars($order->invoice_number ?? 'No invoice yet') ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($order->customer_name ?? '') ?><br>
                            <span class="text-muted small"><?= htmlspecialchars($order->customer_email ?? '') ?></span>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $order->status === 'paid' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars(ucfirst($order->status)) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= (int)$order->item_count ?></td>
                        <td class="text-end">&euro;<?= number_format($order->subtotal, 2) ?></td>
                        <td class="text-end">&euro;<?= number_format($order->vat_total, 2) ?></td>
                        <td class="text-end fw-semibold">&euro;<?= number_format($order->total, 2) ?></td>
                        <td><?= htmlspecialchars($order->created_at ? date('j M Y, H:i', strtotime($order->created_at)) : '') ?></td>
                        <td><?= htmlspecialchars($order->paid_at ? date('j M Y, H:i', strtotime($order->paid_at)) : '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
