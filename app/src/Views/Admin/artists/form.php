<?php
/**
 * @var \App\Models\ArtistModel|null $artist
 */
use App\Middleware\AuthMiddleware;

$isEdit = $artist !== null;
$action = $isEdit ? '/admin/artists/update' : '/admin/artists';
$heading = $isEdit ? 'Edit artist' : 'New artist';
$val = static fn($v) => htmlspecialchars((string)($v ?? ''));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $heading ?></h4>
    <a href="/admin/artists" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form method="POST" action="<?= $action ?>" class="card card-body" style="max-width: 720px;">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$artist->id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= $val($artist->name ?? '') ?>">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Genre</label>
            <input type="text" name="genre" class="form-control" value="<?= $val($artist->genre ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Image path</label>
            <input type="text" name="image" class="form-control" value="<?= $val($artist->image ?? '') ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Bio</label>
        <textarea name="bio" class="form-control" rows="6"><?= $val($artist->bio ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create artist' ?></button>
</form>
