<?php 
require __DIR__ . "/../Partials/header.php"; 
/** @var App\Models\UserModel $user */
?>

<div class="container mt-5">
    <?php if (isset($_GET['mail_sent'])): ?>
        <div class="alert alert-success">
            A verification link has been sent to your email address!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['verified'])): ?>
        <script>
            alert("Success! Your account is now verified. You can log in.");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'expired_token'): ?>
        <div class="alert alert-danger">
            The verification link has expired or is invalid. Please request a new one.
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">User Profile</h3>
                    <span class="badge <?= $user->isActive ? 'bg-success' : 'bg-danger' ?>">
                        <?= $user->isActive ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold text-muted">Full Name</div>
                        <div class="col-sm-8 fs-5">
                            <?= htmlspecialchars($user->FirstName . ' ' . $user->LastName) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 font-weight-bold text-muted">Email Address</div>
                        <div class="col-sm-8">
                            <?= htmlspecialchars($user->Email) ?>
                        </div>
                    </div>

                    <hr>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Role</strong>
                            <span class="text-secondary"><?= htmlspecialchars($user->Role->value ?? $user->Role->name) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Location</strong>
                            <span class="text-secondary"><?= htmlspecialchars($user->Address->value ?? $user->Address->name) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Verification Status</strong>
                            <?php if ($user->isVerified): ?>
                                <span class="badge rounded-pill bg-info text-dark">Verified Account</span>
                            <?php else: ?>
                                <div>
                                    <span class="badge rounded-pill bg-warning text-dark">Pending Verification</span>
                                    <form action="/send-verification-link" method="POST" class="mt-2">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Verify Your Account</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="/updateUser/<?= $user->UserId ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <form method="post" action="/logout">
                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                        <div>
                            <button type="submit" class="btn btn-success px-4">Logout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Automatically close alerts after 5 seconds
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
