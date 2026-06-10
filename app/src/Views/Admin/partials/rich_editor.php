<?php
/**
 * Reusable WYSIWYG editor bound to a hidden textarea. The shared sync script
 * lives in the admin footer.
 *
 * @var string $name   form field name
 * @var string $value  current HTML value (rendered into the editor)
 * @var string $label
 */
$rid = 'rich-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
?>
<div class="mb-3 cms-field">
    <label class="form-label"><?= htmlspecialchars($label) ?></label>
    <div class="cms-toolbar btn-group btn-group-sm mb-2" role="group" aria-label="Formatting">
        <button type="button" class="btn btn-outline-secondary" data-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-command="insertUnorderedList" title="Bullet list"><i class="bi bi-list-ul"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-command="formatBlock" data-value="h3" title="Heading"><i class="bi bi-type-h3"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-command="formatBlock" data-value="p" title="Paragraph"><i class="bi bi-paragraph"></i></button>
    </div>
    <div class="cms-rich-editor form-control mb-1" contenteditable="true" data-target="<?= $rid ?>"><?= $value ?></div>
    <textarea id="<?= $rid ?>" name="<?= htmlspecialchars($name) ?>" hidden><?= htmlspecialchars($value) ?></textarea>
</div>
