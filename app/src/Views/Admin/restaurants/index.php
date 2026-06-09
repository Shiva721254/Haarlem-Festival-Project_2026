<?php
/**
 * @var \App\Models\RestaurantModel[] $restaurants
 */
use App\Middleware\AuthMiddleware;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Restaurants</h4>
    <a href="/admin/restaurants/create" class="btn btn-purple">
        <i class="bi bi-plus-circle"></i> New restaurant
    </a>
</div>

<?php if (empty($restaurants)): ?>
    <div class="alert alert-info">No restaurants yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Cuisine</th>
                    <th>Address</th>
                    <th class="text-end">Stars</th>
                    <th class="text-end">Price/seat</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $restaurant): ?>
                    <tr>
                        <td><?= htmlspecialchars($restaurant->name) ?></td>
                        <td><?= htmlspecialchars($restaurant->cuisine ?? '') ?></td>
                        <td><?= htmlspecialchars($restaurant->address ?? '') ?></td>
                        <td class="text-end"><?= htmlspecialchars((string)($restaurant->stars ?? '')) ?></td>
                        <td class="text-end">
                            <?= $restaurant->price_per_seat !== null ? '&euro;' . number_format($restaurant->price_per_seat, 2) : '' ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="/admin/restaurants/edit/<?= $restaurant->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="/admin/restaurants/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this restaurant?');">
                                    <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $restaurant->id ?>">
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
