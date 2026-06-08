<?php
/** @var \App\Models\UserModel $user */
use App\Middleware\AuthMiddleware;
?>
<div class="container mt-5 mb-5">
    <div class="card shadow-sm mx-auto card-form">
        <div class="card-header text-white panel-header-purple">
            <h4 class="mb-0">My Account</h4>
        </div>
        <div class="card-body">
            <form method="post" action="/account" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= AuthMiddleware::generateCsrfToken(); ?>">

                <div class="text-center mb-4">
                    <?php if (!empty($user->profile_image)): ?>
                        <img src="<?= htmlspecialchars($user->profile_image) ?>" alt="Profile picture"
                             class="rounded-circle mb-2" style="width:96px;height:96px;object-fit:cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle" style="font-size:96px;"></i>
                    <?php endif; ?>
                    <div>
                        <label for="ProfileImage" class="form-label small">Update profile picture (JPG/PNG/WEBP, max 2 MB)</label>
                        <input type="file" class="form-control form-control-sm" id="ProfileImage" name="ProfileImage" accept="image/*">
                    </div>
                </div>

                <h6 class="text-muted">Profile details</h6>
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
                </div>

                <hr class="my-4">
                <h6 class="text-muted">Change password <span class="small">(leave blank to keep current)</span></h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="CurrentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="CurrentPassword" name="CurrentPassword" autocomplete="current-password">
                    </div>
                    <div class="col-md-6">
                        <label for="NewPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="NewPassword" name="NewPassword" autocomplete="new-password">
                    </div>
                    <div class="col-md-6">
                        <label for="NewPasswordConfirm" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="NewPasswordConfirm" name="NewPasswordConfirm" autocomplete="new-password">
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="/" class="btn btn-outline-secondary">Back home</a>
                    <button type="submit" class="btn btn-purple px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
