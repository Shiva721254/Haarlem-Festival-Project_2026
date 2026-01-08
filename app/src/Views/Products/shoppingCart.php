<?php
/** @var array $cartData */
$title = "Your Shopping Cart - Webstore";
require __DIR__ . "/../Partials/header.php"; 
?>

<div class="container">
    <h1>Your Shopping Cart</h1>

    <?php if ($cartData['itemCount'] > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartData['items'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['ProductName']) ?></td>
                        <td>€<?= number_format($item['Price'] / 100, 2) ?></td>
                        <td><?= (int)$item['Quantity'] ?></td>
                        <td>€<?= number_format($item['subtotal'] / 100, 2) ?></td>
                        <td>
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                    <td colspan="2"><strong>€<?= number_format($cartData['grandTotal'] / 100, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="cart-actions">
            <a href="/products" class="btn btn-secondary">Continue Shopping</a>
            <a href="/showCheckout" class="btn btn-success">Proceed to Checkout</a>
        </div>

    <?php else: ?>
        <div class="alert alert-info">
            Your shopping cart is currently empty. <a href="/products">Browse products</a>.
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>