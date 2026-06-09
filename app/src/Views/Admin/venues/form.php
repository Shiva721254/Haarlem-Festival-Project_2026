<?php
/**
 * @var \App\Models\VenueModel|null $venue
 */
use App\Middleware\AuthMiddleware;

$isEdit = $venue !== null;
$action = $isEdit ? '/admin/venues/update' : '/admin/venues';
$heading = $isEdit ? 'Edit venue' : 'New venue';
$val = static fn($v) => htmlspecialchars((string)($v ?? ''));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $heading ?></h4>
    <a href="/admin/venues" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form method="POST" action="<?= $action ?>" class="card card-body" style="max-width: 720px;">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$venue->id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= $val($venue->name ?? '') ?>">
    </div>

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= $val($venue->address ?? '') ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Capacity</label>
            <input type="number" min="0" name="capacity" class="form-control" value="<?= $val($venue->capacity ?? '') ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Image path</label>
        <input type="text" name="image" class="form-control" value="<?= $val($venue->image ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="5"><?= $val($venue->description ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create venue' ?></button>
</form>
