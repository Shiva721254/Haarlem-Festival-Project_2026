<?php
/**
 * Create / edit a ticket type for an event.
 *
 * @var \App\Models\EventModel            $event
 * @var \App\Models\TicketTypeModel|null  $ticket   null = creating
 */
use App\Middleware\AuthMiddleware;

$isEdit  = $ticket !== null;
$action  = $isEdit ? '/admin/tickets/update' : '/admin/tickets';
$heading = $isEdit ? 'Edit ticket type' : 'New ticket type';
$val = static fn($v) => htmlspecialchars((string)($v ?? ''));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $heading ?> <span class="text-muted fs-6">— <?= htmlspecialchars($event->title) ?></span></h4>
    <a href="/admin/events/<?= $event->id ?>/tickets" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form method="POST" action="<?= $action ?>" class="card card-body" style="max-width: 640px;">
    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
    <input type="hidden" name="event_id" value="<?= $event->id ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$ticket->id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required
               placeholder="e.g. Single ticket, Day pass, All-access pass"
               value="<?= $val($ticket->name ?? '') ?>">
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Price (&euro;)</label>
            <input type="number" step="0.01" min="0" name="price" class="form-control" required
                   value="<?= $val($ticket->price ?? '') ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">VAT rate (%)</label>
            <select name="vat_rate" class="form-select">
                <?php $vat = (float)($ticket->vat_rate ?? 21); ?>
                <option value="21" <?= $vat == 21 ? 'selected' : '' ?>>21%</option>
                <option value="9"  <?= $vat == 9  ? 'selected' : '' ?>>9%</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Capacity</label>
            <input type="number" min="0" name="capacity" class="form-control" required
                   value="<?= $val($ticket->capacity ?? '') ?>">
        </div>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
            <?= ($ticket->is_active ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_active">Active (available to buy)</label>
    </div>

    <div>
        <button type="submit" class="btn btn-purple"><?= $isEdit ? 'Save changes' : 'Create ticket type' ?></button>
    </div>
</form>
