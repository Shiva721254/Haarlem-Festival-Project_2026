<?php
$title = "Checkout";
require __DIR__ . "/../Partials/header.php"; 
?>
<div class="checkout-container">
    <h2>Checkout</h2>

    <div class="order-summary">
        <h3>Your Items</h3>
        <ul>
            <?php foreach ($cartData as $item): ?>
                <li>
                    <?= htmlspecialchars($item['ProductName']) ?> (x<?= $item['Quantity'] ?>) - 
                    $<?= number_format($item['Price'] * $item['Quantity'] / 100, 2) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <hr>

    <form action="/processCheckout" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
        <div class="form-group">
            <label for="address">Shipping Address:</label>
            <textarea name="address" id="address" required placeholder="Enter your full address..."></textarea>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method (Hypothetical):</label>
            <select name="payment_method" id="payment_method" required>
                <option value="fake_credit_card">Hypothetical Credit Card</option>
                <option value="fake_paypal">Hypothetical PayPal</option>
                <option value="monopoly_money">Monopoly Money</option>
            </select>
        </div>

        <button type="submit" class="btn-confirm">Place Order (No actual charge)</button>
    </form>
</div>
<?php require __DIR__ . "/../Partials/footer.php"; ?>