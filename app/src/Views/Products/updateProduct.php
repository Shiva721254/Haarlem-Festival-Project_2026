<?php
/** @var ProductViewModel $vm */
$product = $vm->product;
$title = "Edit: " . htmlspecialchars($product->ProductName);
require __DIR__ . "/../Partials/header.php"; 
?>

<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 700px;">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Edit Product: <?= $product->ProductId ?></h4>
        </div>
        <div class="card-body">
            <form action="/saveProduct" method="POST">
                <input type="hidden" name="ProductId" value="<?= $product->ProductId ?>">
                
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="ProductName" class="form-control" 
                           value="<?= htmlspecialchars($product->ProductName) ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Category</label>
                        <select name="Category" class="form-select" required>
                            <?php foreach (\App\Enums\ProductCategory::cases() as $cat): ?>
                                <option value="<?= $cat->value ?>" 
                                    <?= $product->Category === $cat ? 'selected' : '' ?>>
                                    <?= $cat->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Type</label>
                        <select id="TypeSelect" name="Type" class="form-select" required>
                            <?php foreach (\App\Enums\ProductType::cases() as $type): ?>
                                <option value="<?= $type->value ?>" 
                                        data-category="<?= $type->getCategory()->value ?>"
                                        <?= $product->Type === $type ? 'selected' : '' ?>>
                                    <?= $type->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" name="Price" class="form-control" step="0.01" 
                           value="<?= number_format($product->Price / 100, 2, '.', '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="Description" class="form-control" rows="4"><?= htmlspecialchars($product->Description) ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/products" class="btn btn-light border">Back</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.querySelector('select[name="Category"]');
    const typeSelect = document.getElementById('TypeSelect');
    
    // Store all original options in an array
    const allTypeOptions = Array.from(typeSelect.options);

    function filterTypes() {
        const selectedCategory = categorySelect.value;
        
        // 1. Clear current options
        typeSelect.innerHTML = '';

        // 2. Filter options that match the data-category attribute
        const filteredOptions = allTypeOptions.filter(opt => opt.dataset.category === selectedCategory);
        
        // 3. Re-add filtered options to the dropdown
        filteredOptions.forEach(opt => typeSelect.appendChild(opt));

        // 4. If nothing is selected, choose the first available option
        if (filteredOptions.length > 0) {
            typeSelect.disabled = false;
        } else {
            typeSelect.disabled = true; // Disable if no category is picked yet
        }
    }

    // Run when Category changes
    categorySelect.addEventListener('change', filterTypes);
    
    // Initial run
    filterTypes();
});
</script>

<?php require __DIR__ . "/../Partials/footer.php"; ?>