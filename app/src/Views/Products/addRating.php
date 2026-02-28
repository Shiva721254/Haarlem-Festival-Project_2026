<?php require __DIR__ . "/../Partials/header.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-purple mb-4">Rate <?= htmlspecialchars($product->ProductName) ?></h2>
                    
                    <form action="/rateProduct" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                        <input type="hidden" name="id" value="<?= $product->ProductId ?>">

                        <div class="mb-4">
                            <label class="form-label d-block fw-bold">Your Rating</label>
                            <div class="star-rating fs-3 text-warning">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required />
                                    <label for="star<?= $i ?>" class="bi bi-star-fill pointer"></label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="review" class="form-label fw-bold">Written Review (Optional)</label>
                            <textarea name="review" id="review" class="form-control" rows="4" placeholder="What did you think about the product?"></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/product/<?= $product->ProductId ?>" class="text-muted text-decoration-none">Cancel</a>
                            <button type="submit" class="btn btn-purple px-4">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Star Rating CSS Logic (Reverse order for hover effects) */
    .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
    .star-rating input { display: none; }
    .star-rating label { cursor: pointer; color: #ccc; transition: color 0.2s; margin-right: 5px; }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label { color: #ffc107; }
    .pointer { cursor: pointer; }
</style>

<?php require __DIR__ . "/../Partials/footer.php"; ?>