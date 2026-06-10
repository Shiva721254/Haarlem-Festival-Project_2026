<?php
/**
 * Homepage CMS editor.
 *
 * @var string $pageSlug
 * @var array<string,\App\Models\ContentBlockModel> $blocks
 */
use App\Middleware\AuthMiddleware;

$labels = [
    'hero' => 'Hero',
    'intro' => 'Intro',
    'practical' => 'Practical information',
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Homepage content</h4>
        <p class="text-muted mb-0">Edit public homepage HTML and featured images.</p>
    </div>
    <a href="/" class="btn btn-outline-secondary btn-sm" target="_blank">View homepage</a>
</div>

<form method="POST" action="/admin/save" enctype="multipart/form-data" class="cms-editor-form">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">

    <?php foreach ($labels as $key => $label): ?>
        <?php $block = $blocks[$key] ?? null; ?>
        <section class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= htmlspecialchars($label) ?></strong>
                <?php if (!empty($block?->updated_at)): ?>
                    <span class="text-muted small">Updated <?= htmlspecialchars(date('j M Y, H:i', strtotime($block->updated_at))) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="cms-toolbar btn-group btn-group-sm mb-2" role="group" aria-label="<?= htmlspecialchars($label) ?> formatting">
                    <button type="button" class="btn btn-outline-secondary" data-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-command="insertUnorderedList" title="Bullet list"><i class="bi bi-list-ul"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-command="formatBlock" data-value="h2" title="Heading"><i class="bi bi-type-h2"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-command="formatBlock" data-value="p" title="Paragraph"><i class="bi bi-paragraph"></i></button>
                </div>

                <div class="cms-rich-editor form-control mb-2" contenteditable="true" data-target="block-<?= htmlspecialchars($key) ?>">
                    <?= $block?->html ?? '' ?>
                </div>
                <textarea id="block-<?= htmlspecialchars($key) ?>" name="blocks[<?= htmlspecialchars($key) ?>]" hidden><?= htmlspecialchars($block?->html ?? '') ?></textarea>

                <div class="row align-items-end g-3 mt-1">
                    <div class="col-md-7">
                        <label class="form-label">Image</label>
                        <input type="file" name="images[<?= htmlspecialchars($key) ?>]" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-md-5">
                        <?php if (!empty($block?->image_path)): ?>
                            <div class="cms-image-preview">
                                <img src="<?= htmlspecialchars($block->image_path) ?>" alt="<?= htmlspecialchars($label) ?>">
                                <span><?= htmlspecialchars($block->image_path) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">No image selected.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-purple">
        <i class="bi bi-save"></i> Save homepage
    </button>
</form>

<?php /* Editor sync script is shared in the admin footer. */ ?>
