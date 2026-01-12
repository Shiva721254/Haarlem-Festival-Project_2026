<?php
use App\ViewModels\UsersViewModel;
/** @var UsersViewModel $vm */

// Include the HTML head and Bootstrap CSS
require __DIR__ . "/../Partials/header.php";
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👥 User Overview</h2>
        <a href="createUser" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New User
        </a>
    </div>

    <?php
    // Check if the users property exists and is iterable
    if (empty($vm->users)):
    ?>
        <div class="alert alert-info text-center mt-5" role="alert">
            No users found yet!
        </div>
    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Address</th>
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
                                <?= $user->Address ? htmlspecialchars($user->Address->name) : '' ?>
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

                                    <a href="<?= htmlspecialchars('/delete/' . $user_id) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Delete User">
                                        <i class="bi bi-trash"></i> Delete
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
// Include the closing tags and Bootstrap JavaScript
require __DIR__ . "/../Partials/footer.php";
?>