<?php
use App\ViewModels\UsersViewModel;
/** @var UsersViewModel $vm */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👥 User Overview</h2>
        <a href="createUser" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New User
        </a>
    </div>

    <form method="GET" action="/users" class="row g-2 mb-4">
        <div class="col-sm-5">
            <input type="text" name="q" class="form-control" placeholder="Search name or email"
                   value="<?= htmlspecialchars($vm->search) ?>">
        </div>
        <div class="col-sm-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                <?php foreach (\App\Enums\UserRole::cases() as $r): ?>
                    <option value="<?= $r->value ?>" <?= $vm->role === $r->value ? 'selected' : '' ?>><?= $r->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4 d-flex gap-2">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> Search</button>
            <?php if ($vm->search !== '' || $vm->role !== ''): ?>
                <a href="/users" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($vm->users)): ?>
        <div class="alert alert-info text-center" role="alert">
            No users match your search.
        </div>
    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th><a class="text-white text-decoration-none" href="<?= $vm->sortLink('FirstName') ?>">First Name<?= $vm->sortIndicator('FirstName') ?></a></th>
                        <th><a class="text-white text-decoration-none" href="<?= $vm->sortLink('LastName') ?>">Last Name<?= $vm->sortIndicator('LastName') ?></a></th>
                        <th><a class="text-white text-decoration-none" href="<?= $vm->sortLink('Email') ?>">Email<?= $vm->sortIndicator('Email') ?></a></th>
                        <th><a class="text-white text-decoration-none" href="<?= $vm->sortLink('Role') ?>">Role<?= $vm->sortIndicator('Role') ?></a></th>
                        <th><a class="text-white text-decoration-none" href="<?= $vm->sortLink('created_at') ?>">Registered<?= $vm->sortIndicator('created_at') ?></a></th>
                        <th class="text-center">Verified</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Iterate over the users in the ViewModel
                    foreach ($vm->users as $user):
                        // Safely get User ID for links
                        $user_id = $user->UserId ?? 0;
                        //$entity_base_url = '/users'; // Base URL for User actions
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($user_id) ?></td>
                            <td><?= htmlspecialchars($user->FirstName ?? '') ?></td>
                            <td><?= htmlspecialchars($user->LastName ?? '') ?></td>
                            <td><?= htmlspecialchars($user->Email ?? '') ?></td>
                            <td>
                                <?= $user->Role ? htmlspecialchars($user->Role->name) : '' ?>
                            </td>
                            <td>
                                <?= $user->created_at ? htmlspecialchars(date('j M Y', strtotime($user->created_at))) : '—' ?>
                            </td>

                            <td class="text-center">
                                <?php
                                $isVerified = $user->isVerified ?? false;
                                $verified_class = $isVerified ? 'text-bg-success' : 'text-bg-danger';
                                $verified_text = $isVerified ? 'Yes' : 'No';
                                ?>
                                <span class="badge <?= $verified_class ?>">
                                    <?= $verified_text ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php
                                $isActive = $user->isActive ?? false;
                                $active_class = $isActive ? 'text-bg-success' : 'text-bg-warning';
                                $active_text = $isActive ? 'Yes' : 'No';
                                ?>
                                <span class="badge <?= $active_class ?>">
                                    <?= $active_text ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="User Actions">
                                    <a href="/updateUser/<?= $user->UserId ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    <form method="POST" action="/deleteUser" class="d-inline"
                                          onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $user_id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                            <i class="bi bi-trash"></i> Delete
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
</div>