<?php
$title = "Order Success";
require __DIR__ . "/../Partials/header.php"; 
?>
<div class="success-message">
    <div class="icon">✅</div>
    <h2>Thank you for your order!</h2>
    <p>Your hypothetical payment was successful.</p>
    <p><strong>Order ID:</strong> #<?= htmlspecialchars($orderId) ?></p>
    
    <div class="next-steps">
        <p>Your cart has been emptied, and your items are now being "processed" for shipping.</p>
        <a href="/products" class="btn">Continue Shopping</a>
    </div>
</div>
<?php require __DIR__ . "/../Partials/footer.php"; ?>