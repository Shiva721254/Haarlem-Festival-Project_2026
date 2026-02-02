<?php 
require __DIR__ . "/../Partials/header.php"; 
/** @var App\Models\ProductModel $product */
/** @var App\Models\ProductRating[] $reviews */
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
                <img src="https://placehold.co/600x450?text=<?= urlencode($product->ProductName) ?>" 
                     class="img-fluid" 
                     alt="<?= htmlspecialchars($product->ProductName) ?>">
            </div>
        </div>

        <div class="col-md-6">
            <div class="product-info-wrapper">
                <span class="badge bg-secondary text-uppercase mb-2"><?= htmlspecialchars($product->Category->name) ?></span>
                <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($product->ProductName) ?></h1>

                <div class="mb-4 text-muted">
                    <h5 class="text-dark">Description</h5>
                    <p class="lh-base"><?= nl2br(htmlspecialchars($product->Description)) ?></p>

                    <a href="#reviews-section" class="text-decoration-none">
                        <div class="d-flex align-items-center mt-2">
                            <span class="text-warning me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= ($i <= round($avgRating)) ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span id="rating-number" class="text-purple fw-bold"><?= number_format($avgRating, 1) ?></span>
                            <span id="review-count" class="ms-2 text-muted">(<?= $totalReviews ?> reviews)</span>
                            <script>
                                const currentProductId = <?= (int)$product->ProductId ?>; 
                                setInterval(() => updateRating(currentProductId), 10000);
                            </script>
                        </div>
                    </a> 
                </div>

                <form action="/cart/add" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                    <input type="hidden" name="product_id" value="<?= $product->ProductId ?>">
                    
                    <div class="mb-4 border-top pt-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <span class="fw-bold text-primary fs-5">
                                    <?= "Price: €" . number_format($product->Price / 100, 2) ?>
                                </span>
                            </div>
                            <div class="col-auto ms-auto">
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
    #reviews-section { scroll-margin-top: 100px; }
    html { scroll-behavior: smooth; }
    .border-dashed { border-style: dashed !important; }
</style>

<hr class="my-5">

<div id="reviews-section" class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="m-0">
                    Customer Reviews 
                    <span id="review-count">(<?= $totalReviews ?> reviews)</span>
                </h3>
                <a href="/addRating/<?= $product->ProductId ?>" class="btn btn-success">
                    <i class="bi bi-pencil-square me-2"></i> Write a Review
                </a>
            </div>

            <?php if (empty($reviews)): ?>
                <p class="text-muted">No reviews yet for this product.</p>
            <?php else: ?>
                <div id="reviews-list" class="list-group list-group-flush">
                    <?php foreach ($reviews as $review): ?>
                        <div class="list-group-item px-0 py-4 border-0 border-bottom">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= htmlspecialchars($review->FirstName . ' ' . $review->LastName) ?></strong>
                                    <div class="text-warning small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= ($i <= $review->Rating) ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">
                                        <?= $review->CreatedAt ? date('F j, Y', strtotime($review->CreatedAt)) : 'Date unknown' ?>
                                    </small>
                                    
                                    <div class="btn-group btn-group-sm mt-2">
                                        <a href="/editRating/<?= $product->ProductId ?>" class="btn btn-outline-primary py-0">Edit</a>
                                        <form action="/deleteRating/<?= $product->ProductId ?>" 
                                            method="POST" 
                                            class="d-inline ajax-form" 
                                            data-product-id="<?= $product->ProductId ?>">
                                            
                                            <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                                            <button type="submit" class="btn btn-outline-danger py-0">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <p class="mb-0 text-secondary">
                                <?= nl2br(htmlspecialchars($review->Review ?? 'No written comment.')) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>