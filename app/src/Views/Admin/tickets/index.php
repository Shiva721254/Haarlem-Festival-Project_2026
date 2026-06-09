<?php
/**
 * Admin ticket-type list for one event.
 *
 * @var \App\Models\EventModel         $event
 * @var \App\Models\TicketTypeModel[]  $tickets
 */
use App\Middleware\AuthMiddleware;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="/admin/events" class="small text-decoration-none">&larr; Events</a>
        <h4 class="mb-0">Ticket types — <?= htmlspecialchars($event->title) ?></h4>
    </div>
    <a href="/admin/events/<?= $event->id ?>/tickets/create" class="btn btn-purple">
        <i class="bi bi-plus-circle"></i> New ticket type
    </a>
</div>

<?php if (empty($tickets)): ?>
    <div class="alert alert-info">No ticket types yet for this event.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">VAT</th>
                    <th class="text-end">Capacity</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Available</th>
                    <th class="text-center">Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t->name) ?></td>
                        <td class="text-end">&euro;<?= number_format($t->price, 2) ?></td>
                        <td class="text-end"><?= rtrim(rtrim(number_format($t->vat_rate, 2), '0'), '.') ?>%</td>
                        <td class="text-end"><?= $t->capacity ?></td>
                        <td class="text-end"><?= $t->sold ?></td>
                        <td class="text-end"><?= $t->available() ?></td>
                        <td class="text-center">
                            <span class="badge text-bg-<?= $t->is_active ? 'success' : 'secondary' ?>">
                                <?= $t->is_active ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/admin/tickets/edit/<?= $t->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="/admin/tickets/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this ticket type?');">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $t->id ?>">
                                    <input type="hidden" name="event_id" value="<?= $event->id ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
