<?php
$title = "Order Success";
require __DIR__ . "/../Partials/header.php"; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold">Thank you for your order!</h2>
                    <p class="text-muted mb-4">Your hypothetical payment was processed successfully.</p>
                    
                    <div class="bg-light p-3 rounded mb-4">
                        <p class="mb-0 text-uppercase small fw-bold text-muted">Order ID</p>
                        <h4 class="mb-0 text-primary">#<?= htmlspecialchars($orderId) ?></h4>
                    </div>

                    <div class="next-steps mb-4 text-start">
                        <p class="text-muted"><i class="bi bi-cart-x me-2"></i> Your cart has been emptied.</p>
                        <p class="text-muted"><i class="bi bi-box-seam me-2"></i> Items are being prepared for shipping.</p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/products" class="btn btn-primary btn-lg">Continue Shopping</a>
                        <a href="/my-orders" class="btn btn-outline-secondary">View Order History</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>