<?php
$title = "Checkout";
require __DIR__ . "/../Partials/header.php"; 
?>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-md-5 col-lg-4 order-md-last">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-primary">Your cart</span>
                <span class="badge bg-primary rounded-pill"><?= $cartData['itemCount'] ?></span>
            </h4>
            <ul class="list-group mb-3">
                <?php foreach ($cartData['items'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                            <h6 class="my-0"><?= htmlspecialchars($item['ProductName'] ?? 'Unknown Product') ?></h6>
                            <small class="text-muted">Quantity: <?= $item['Quantity'] ?></small>
                        </div>
                        <span class="text-muted">$<?= number_format(($item['Price'] * $item['Quantity']) / 100, 2) ?></span>
                    </li>
                <?php endforeach; ?>
                
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <span class="fw-bold">Total (USD)</span>
                    <strong>$<?= number_format($cartData['grandTotal'] / 100, 2) ?></strong>
                </li>
            </ul>
        </div>

        <div class="col-md-7 col-lg-8">
            <h4 class="mb-3">Shipping & Payment</h4>
            <form action="/processCheckout" method="POST" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="address" class="form-label">Shipping Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3" required 
                                  placeholder="1234 Main St, City, Country"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" id="payment_method" required>
                            <option value="">Choose...</option>
                            <option value="fake_credit_card">Hypothetical Credit Card</option>
                            <option value="fake_paypal">Hypothetical PayPal</option>
                            <option value="monopoly_money">Monopoly Money</option>
                        </select>
                        <small class="text-muted">No actual charge will be made.</small>
                    </div>
                </div>

                <hr class="my-4">

                <button class="w-100 btn btn-primary btn-lg" type="submit">Complete Purchase</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>