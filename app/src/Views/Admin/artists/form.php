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
        <textarea name="bio" class="form-control" rows="3"><?= $val($artist->bio ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Career highlights</label>
        <textarea name="career_highlights" class="form-control" rows="5"><?= $val($artist->career_highlights ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Important tracks</label>
        <input type="text" name="tracks" class="form-control" value="<?= $val($artist->tracks ?? '') ?>"
               placeholder="Separate with semicolons, e.g. Track One; Track Two">
    </div>

    <div class="mb-3">
        <label class="form-label">Audio sample URL (simulated listen)</label>
        <input type="text" name="audio_url" class="form-control" value="<?= $val($artist->audio_url ?? '') ?>"
               placeholder="https://… .mp3">
    </div>

    <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create artist' ?></button>
</form>

<?php if ($isEdit): ?>
    <?php $gallery = $gallery ?? []; ?>
    <div class="card card-body mt-4" style="max-width: 720px;">
        <h5 class="mb-3">Gallery images</h5>
        <p class="text-muted small">Add at least three images. JPG, PNG or WEBP, max 4&nbsp;MB each.</p>

        <?php if (!empty($gallery)): ?>
            <div class="row g-3 mb-3">
                <?php foreach ($gallery as $img): ?>
                    <div class="col-4 col-md-3">
                        <img src="<?= $val($img['path']) ?>" alt="" class="img-fluid rounded mb-1" style="object-fit:cover;aspect-ratio:3/2;">
                        <form method="POST" action="/admin/artists/images/delete"
                              onsubmit="return confirm('Remove this image?');">
                            <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                            <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                            <input type="hidden" name="artist_id" value="<?= (int)$artist->id ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No images yet.</p>
        <?php endif; ?>

        <form method="POST" action="/admin/artists/<?= (int)$artist->id ?>/images" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
            <input type="file" name="gallery_image" accept="image/jpeg,image/png,image/webp" class="form-control" required>
            <button type="submit" class="btn btn-purple">Upload</button>
        </form>
    </div>
<?php endif; ?>
