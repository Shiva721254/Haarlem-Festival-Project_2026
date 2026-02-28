<?php 
/** @var ManageRatingViewModel $vm */
$rating = $vm->ratingModel;
$productId = $rating ? $rating->ProductId : 0;
$title = "Edit Review for Product #$productId";
require __DIR__ . "/../Partials/header.php"; 
?>
<div class="container mt-5">
    <h2>Edit Your Review</h2>
    <form action="/updateRating/<?= $productId ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
        
        <div class="mb-3">
            <label class="form-label">Rating (1-5)</label>
            <select name="rating" class="form-select">
                <?php for($i=1; $i<=5; $i++): ?>
                    <option value="<?= $i ?>" <?= $rating->Rating == $i ? 'selected' : '' ?>><?= $i ?> Stars</option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Review</label>
            <textarea name="review" class="form-control" rows="4"><?= htmlspecialchars($rating->Review) ?></textarea>
        </div>

        <button type="submit" class="btn btn-purple">Update Review</button>
        <a href="/product/<?= $productId ?>" class="btn btn-link">Cancel</a>
    </form>
</div>
<?php 
require __DIR__ . "/../Partials/footer.php"; 
?>