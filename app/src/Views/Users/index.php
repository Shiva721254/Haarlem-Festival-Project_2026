<?php
use App\ViewModels\UsersViewModel;
/** @var UsersViewModel $vm */

$title = "User Overview";
require __DIR__ . "/../Partials/header.php";
?>
<link rel="stylesheet" href="/assets/css/userCRUD.css">
<div class="container mt-5"> <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h2 class="fw-bold" style="color: var(--jazz-purple); letter-spacing: 1px;">👥 User Management</h2>
        <a href="createUser" class="btn btn-primary shadow-sm px-4 py-2">
            <i class="bi bi-plus-circle me-2"></i> Create New User
        </a>
    </div>

    <div class="card filter-card">
        <div class="card-body p-4">
            <form method="GET" action="/users" class="row g-3 align-items-end">
                
                <div class="col-md-5">
                    <label class="filter-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search icon-purple"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0" 
                            placeholder="Name or email..." 
                            value="<?= htmlspecialchars($vm->searchTerm ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="filter-label">Role Filter</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <?php foreach (\App\Enums\UserRole::cases() as $role): ?>
                            <option value="<?= $role->value ?>" <?= ($vm->roleFilter === $role->value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-filter me-2"></i>Apply
                    </button>
                    <a href="/users" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    <?php if (empty($vm->users)): ?>
        <div class="alert shadow-sm text-center py-5 mt-5" style="background-color: var(--off-white); border: 1px solid var(--jazz-purple);" role="alert">
            <i class="bi bi-info-circle fs-4 d-block mb-3"></i> No users found in the festival database.
        </div>
    <?php else: ?>

        <div class="table-responsive card shadow-sm border-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Verified</th>
                        <th class="text-center">Active</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vm->users as $user): 
                        $user_id = $user->UserId ?? 0;
                    ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= htmlspecialchars($user_id) ?></td>
                            <td><?= htmlspecialchars($user->FirstName ?? '') ?></td>
                            <td><?= htmlspecialchars($user->LastName ?? '') ?></td>
                            <td class="text-lowercase"><?= htmlspecialchars($user->Email ?? '') ?></td>
                            <td>
                                <span class="small text-uppercase fw-bold text-dark">
                                    <?= $user->Role ? htmlspecialchars($user->Role->name) : '—' ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php
                                $isVerified = $user->isVerified ?? false;
                                $v_badge = $isVerified ? 'badge-jazz-success' : 'badge-jazz-danger';
                                ?>
                                <span class="badge <?= $v_badge ?> px-3 py-2"> <?= $isVerified ? 'Yes' : 'No' ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php
                                $isActive = $user->isActive ?? false;
                                $a_badge = $isActive ? 'badge-jazz-success' : 'badge-jazz-warn';
                                ?>
                                <span class="badge <?= $a_badge ?> px-3 py-2"> <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>

                            <td class="text-center pe-4">
                                <div class="btn-group" role="group">
                                    <a href="/updateUser/<?= $user->UserId ?>" class="btn btn-sm btn-outline-secondary py-2 px-3" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/deleteUser/<?= $user_id ?>" class="btn btn-sm btn-outline-danger py-2 px-3" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php
require __DIR__ . "/../Partials/footer.php";
?>