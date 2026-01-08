<?php 
require __DIR__ . "/../Partials/header.php"; 
/** @var App\Models\ProductModel $product */
?>

<div class="container mt-5 mb-5">
    <?php if (isset($_GET['added']) && $_GET['added'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Product added to your shopping cart.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/products" class="text-purple text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product->ProductName) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm overflow-hidden">
                <img src="https://via.placeholder.com/600x450?text=<?= urlencode($product->ProductName) ?>" 
                     class="img-fluid" 
                     alt="<?= htmlspecialchars($product->ProductName) ?>">
            </div>
        </div>

        <div class="col-md-6">
            <div class="product-info-wrapper">
                <span class="badge bg-secondary text-uppercase mb-2"><?= htmlspecialchars($product->Category->name) ?></span>
                <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($product->ProductName) ?></h1>

                <div class="mb-4 text-muted">
                    <h5 class="text-dark">Product Description</h5>
                    <p class="lh-base"><?= nl2br(htmlspecialchars($product->Description)) ?></p>
                </div>

                <form action="/cart/add" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product->ProductId ?>">
                    
                    <div class="mb-4 border-top pt-4">
                        <div class="row g-3 align-items-center">
                            <span class="fw-bold text-primary fs-5">
                                <?= "Price: €" .number_format($product->Price / 100, 2) ?>
                            </span>
                            <div class="col-auto">
                                <label for="quantity" class="form-label mb-0 fw-semibold">Quantity</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" style="width: 80px;">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-purple btn-lg py-3 shadow-sm">
                            <i class="bi bi-cart-plus me-2"></i> Add to Shopping Cart
                        </button>
                        <button class="btn btn-outline-secondary btn-lg py-3" type="button">
                            <i class="bi bi-heart me-2"></i> Add to Wishlist
                        </button>
                    </div>
                </form>

                <?php if (isset($_SESSION['Role']) && ($_SESSION['Role'] === 'admin' || (isset($_SESSION['Role']->value) && $_SESSION['Role']->value === 'admin'))): ?>
                    <div class="mt-4 p-3 bg-light rounded border border-dashed text-center">
                        <p class="text-muted small mb-2">Administrator Actions</p>
                        <a href="/updateProduct/<?= $product->ProductId ?>" class="btn btn-sm btn-outline-primary">Edit Product</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .text-purple { color: #5c2379; }
    .btn-purple { background-color: #5c2379; color: white; border: none; }
    .btn-purple:hover { background-color: #4a1c61; color: white; }
    .breadcrumb-item a:hover { text-decoration: underline !important; }
</style>

<?php require __DIR__ . "/../Partials/footer.php"; ?>