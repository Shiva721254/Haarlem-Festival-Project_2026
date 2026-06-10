<?php
/**
 * Create / edit event form.
 *
 * @var \App\Models\EventModel|null $event   null = creating, otherwise editing
 * @var array{types:array,venues:array,restaurants:array,artists:array} $options
 *
 * TODO (you): restyle/lay this out to taste. It is fully functional as-is.
 * Image is a path field for now — the image-upload ticket will replace it.
 */
use App\Middleware\AuthMiddleware;

$isEdit  = $event !== null;
$action  = $isEdit ? '/admin/events/update' : '/admin/events';
$heading = $isEdit ? 'Edit event' : 'New event';

// Helper: pre-fill a value safely, falling back to '' when creating.
$val = static fn($v) => htmlspecialchars((string)($v ?? ''));

// datetime-local inputs want "Y-m-d\TH:i".
$dt = static fn(?string $s) => $s ? date('Y-m-d\TH:i', strtotime($s)) : '';
$selectedArtistIds = array_map(static fn($a) => (int)$a->id, $event->artists ?? []);
if (!empty($event?->artist_ids)) {
    $selectedArtistIds = array_map('intval', $event->artist_ids);
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $heading ?></h4>
    <a href="/admin/events" class="btn btn-outline-secondary btn-sm">Back to list</a>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="card card-body" style="max-width: 760px;">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$event->id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?= $val($event->title ?? '') ?>">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Event type</label>
            <select name="event_type_id" class="form-select" required>
                <option value="">Choose…</option>
                <?php foreach ($options['types'] as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($event->event_type_id ?? 0) == $t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Venue <span class="text-muted">(optional)</span></label>
            <select name="venue_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($options['venues'] as $v): ?>
                    <option value="<?= (int)$v['id'] ?>" <?= ($event->venue_id ?? 0) == $v['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Restaurant <span class="text-muted">(optional)</span></label>
            <select name="restaurant_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($options['restaurants'] as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= ($event->restaurant_id ?? 0) == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Starts at</label>
            <input type="datetime-local" name="starts_at" class="form-control" required
                   value="<?= $dt($event->starts_at ?? null) ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Ends at <span class="text-muted">(optional)</span></label>
            <input type="datetime-local" name="ends_at" class="form-control"
                   value="<?= $dt($event->ends_at ?? null) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Image</label>
        <?php if (!empty($event->image)): ?>
            <div class="mb-2"><img src="<?= $val($event->image) ?>" alt="" style="max-height:90px" class="rounded border"></div>
        <?php endif; ?>
        <input type="file" name="image_file" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
        <input type="text" name="image" class="form-control" value="<?= $val($event->image ?? '') ?>"
               placeholder="…or paste an image path / URL">
    </div>

    <div class="mb-3">
        <label class="form-label">Artists <span class="text-muted">(optional)</span></label>
        <select name="artist_ids[]" class="form-select" multiple size="5">
            <?php foreach ($options['artists'] as $artist): ?>
                <option value="<?= (int)$artist['id'] ?>" <?= in_array((int)$artist['id'], $selectedArtistIds, true) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($artist['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Hold Ctrl/Cmd to select multiple artists.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="5"><?= $val($event->description ?? '') ?></textarea>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1"
            <?= ($event->is_published ?? false) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_published">Published (visible on the public site)</label>
    </div>

    <div>
        <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create event' ?></button>
    </div>
</form>
