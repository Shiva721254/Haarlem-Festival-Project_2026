<?php
/** @var ProductViewModel $vm */
$title = "Electronics - Webstore";
require __DIR__ . "/../Partials/header.php"; 
//print_r($_SESSION);
?>

<style>
    /* Custom purple theme matching your header */
    .btn-purple { background-color: #5c2379; color: white; }
    .btn-purple:hover { background-color: #4a1c61; color: white; }
    .text-purple { color: #5c2379; }
    
    /* Truncate description to exactly 3 lines with "..." */
    .description-truncate {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        min-height: 4.5em; /* Ensures cards stay aligned */
    }
    
    .hero-section {
        background: linear-gradient(rgba(92, 35, 121, 0.8), rgba(92, 35, 121, 0.8)), 
                    url('https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 60px 0;
    }
</style>

<section class="hero-section text-center mb-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Upgrade Your Tech</h1>
        <p class="lead">Discover the latest in high-end electronics and gadgets.</p>
        <a href="#product-grid" class="btn btn-light btn-lg">Shop Now</a>
    </div>
</section>

<div class="container" id="product-grid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        
        <div>
            <h2 class="text-purple fw-bold"><i class="bi bi-cpu"></i> Featured Products</h2>
            <?php if (!empty($vm->searchTerm)): ?>
                <p class="text-muted">
                    Showing results for: <strong><?= htmlspecialchars($vm->searchTerm) ?></strong> 
                    <a href="/products" class="text-danger ms-2" style="text-decoration: none;">&times; Clear</a>
                </p>
            <?php endif; ?>
        </div>

        <h2 class="text-purple fw-bold"><i class="bi bi-cpu"></i> Featured Products</h2>
        <?php if (isset($_SESSION['Role']) && $_SESSION['Role']->value === 'admin'): ?>
            <a href="/createProduct" class="btn btn-purple">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
        <?php endif; ?>
    </div>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($vm->products as $product): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body pb-0">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge rounded-pill bg-secondary text-uppercase" style="font-size: 0.7rem;">
                                <?= htmlspecialchars($product->Category ?? 'General') ?>
                            </span>
                            <span class="fw-bold text-primary fs-5">
                                €<?= number_format($product->Price / 100, 2) ?>
                            </span>
                        </div>
                    </div>

                    <img src="https://via.placeholder.com/300x200?text=<?= urlencode($product->ProductName) ?>" 
                         class="card-img-top p-3" alt="<?= htmlspecialchars($product->ProductName) ?>">

                    <div class="card-body pt-0">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($product->ProductName) ?></h5>
                        
                        <p class="card-text text-muted small description-truncate">
                            <?= htmlspecialchars($product->Description ?? 'No description available for this electronic item.') ?>
                        </p>
                    </div>

                    <div class="card-footer bg-transparent border-0 pb-3">
                        <div class="d-grid gap-2">
                            <a href="/product/<?= $product->ProductId ?>" class="btn btn-primary">Details</a>
                            
                            <?php if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'admin'): ?>
                                <div class="btn-group w-100 mt-2">
                                    <a href="/updateProduct/<?= $product->ProductId ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="/deleteProduct" method="POST" class="d-inline w-100" onsubmit="return confirm('Delete?');">
                                        <input type="hidden" name="ProductId" value="<?= $product->ProductId ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>