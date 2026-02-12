<?php
use App\ViewModels\UsersViewModel;
/** @var UsersViewModel $vm */

$title = "User Overview";
require __DIR__ . "/../Partials/header.php";
?>

<div class="container mt-5"> <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h2 class="fw-bold" style="color: var(--jazz-purple); letter-spacing: 1px;">👥 User Management</h2>
        <a href="createUser" class="btn btn-primary shadow-sm px-4 py-2">
            <i class="bi bi-plus-circle me-2"></i> Create New User
        </a>
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