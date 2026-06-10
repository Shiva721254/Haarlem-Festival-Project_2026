<?php
/**
 * @var \App\Models\RestaurantModel|null $restaurant
 */
use App\Middleware\AuthMiddleware;

$isEdit = $restaurant !== null;
$action = $isEdit ? '/admin/restaurants/update' : '/admin/restaurants';
$heading = $isEdit ? 'Edit restaurant' : 'New restaurant';
$val = static fn($v) => htmlspecialchars((string)($v ?? ''));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $heading ?></h4>
    <a href="/admin/restaurants" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="card card-body" style="max-width: 760px;">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$restaurant->id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= $val($restaurant->name ?? '') ?>">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Cuisine</label>
            <input type="text" name="cuisine" class="form-control" value="<?= $val($restaurant->cuisine ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= $val($restaurant->address ?? '') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Stars</label>
            <input type="number" min="0" max="5" name="stars" class="form-control" value="<?= $val($restaurant->stars ?? '') ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Price per seat (&euro;)</label>
            <input type="number" min="0" step="0.01" name="price_per_seat" class="form-control" value="<?= $val($restaurant->price_per_seat ?? '') ?>">
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Image</label>
            <?php if (!empty($restaurant->image)): ?>
                <div class="mb-2"><img src="<?= $val($restaurant->image) ?>" alt="" style="max-height:90px" class="rounded border"></div>
            <?php endif; ?>
            <input type="file" name="image_file" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
            <input type="text" name="image" class="form-control" value="<?= $val($restaurant->image ?? '') ?>"
                   placeholder="…or paste an image path / URL">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="5"><?= $val($restaurant->description ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create restaurant' ?></button>
</form>
