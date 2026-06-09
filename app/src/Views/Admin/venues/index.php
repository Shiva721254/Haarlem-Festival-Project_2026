<?php
/**
 * @var \App\Models\VenueModel[] $venues
 */
use App\Middleware\AuthMiddleware;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Venues</h4>
    <a href="/admin/venues/create" class="btn btn-purple">
        <i class="bi bi-plus-circle"></i> New venue
    </a>
</div>

<?php if (empty($venues)): ?>
    <div class="alert alert-info">No venues yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th class="text-end">Capacity</th>
                    <th>Image</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($venues as $venue): ?>
                    <tr>
                        <td><?= htmlspecialchars($venue->name) ?></td>
                        <td><?= htmlspecialchars($venue->address ?? '') ?></td>
                        <td class="text-end"><?= htmlspecialchars((string)($venue->capacity ?? '')) ?></td>
                        <td><?= htmlspecialchars($venue->image ?? '') ?></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/admin/venues/edit/<?= $venue->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="/admin/venues/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this venue?');">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $venue->id ?>">
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
