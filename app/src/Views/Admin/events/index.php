<?php
/**
 * Admin events list.
 *
 * @var \App\Models\EventModel[] $events
 */
use App\Middleware\AuthMiddleware;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Events</h4>
    <a href="/admin/events/create" class="btn btn-purple">
        <i class="bi bi-plus-circle"></i> New event
    </a>
</div>

<?php if (empty($events)): ?>
    <div class="alert alert-info">No events yet. Create your first one.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Starts</th>
                    <th class="text-center">Published</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event->title) ?></td>
                        <td><?= htmlspecialchars($event->event_type_name ?? '') ?></td>
                        <td><?= htmlspecialchars(date('j M Y, H:i', strtotime($event->starts_at))) ?></td>
                        <td class="text-center">
                            <?php if ($event->is_published): ?>
                                <span class="badge text-bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/event/<?= $event->id ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <a href="/admin/events/edit/<?= $event->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="/admin/events/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this event?');">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $event->id ?>">
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
