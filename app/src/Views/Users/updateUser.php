<?php
/** @var ManageUserViewModel $vm */
$user = $vm->user;
?>
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto card-form">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Edit User Details</h4>
            </div>
            <div class="card-body">
                <form method="post" action="/saveUser">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                    <input type="hidden" name="UserId" value="<?= htmlspecialchars($user->UserId) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="FirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="FirstName" name="FirstName" 
                                   value="<?= htmlspecialchars($user->FirstName) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="LastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="LastName" name="LastName" 
                                   value="<?= htmlspecialchars($user->LastName) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="Email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="Email" name="Email" 
                                   value="<?= htmlspecialchars($user->Email) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="Role" class="form-label">Role</label>
                            <select class="form-select" id="Role" name="Role" required>
                                <?php foreach (\App\Enums\UserRole::cases() as $role): ?>
                                    <option value="<?= $role->value ?>" <?= $user->Role === $role ? 'selected' : '' ?>>
                                        <?= $role->name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>                      
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="/users" class="btn btn-outline-secondary">Back to List</a>
                        <button type="submit" class="btn btn-success px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
