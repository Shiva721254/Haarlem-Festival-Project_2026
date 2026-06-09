<?php
/**
 * @var \App\Models\ArtistModel[] $artists
 */
use App\Middleware\AuthMiddleware;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Artists</h4>
    <a href="/admin/artists/create" class="btn btn-purple">
        <i class="bi bi-plus-circle"></i> New artist
    </a>
</div>

<?php if (empty($artists)): ?>
    <div class="alert alert-info">No artists yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Genre</th>
                    <th>Image</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artists as $artist): ?>
                    <tr>
                        <td><?= htmlspecialchars($artist->name) ?></td>
                        <td><?= htmlspecialchars($artist->genre ?? '') ?></td>
                        <td><?= htmlspecialchars($artist->image ?? '') ?></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/admin/artists/edit/<?= $artist->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="/admin/artists/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this artist?');">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $artist->id ?>">
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
