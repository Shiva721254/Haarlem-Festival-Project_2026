<?php
/** @var ManageUserViewModel $vm */
$title = "Remove User";
require __DIR__ . "/../Partials/header.php";
?>
<link rel="stylesheet" href="/assets/css/userCRUD.css">
<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-header">
            <h4 class="mb-0 text-center">Confirm Deletion</h4>
        </div>
        <div class="card-body">
            <div class="text-center py-4">
                <p class="fs-5">
                    Are you sure you want to delete user 
                    <span class="fw-bold"><?= htmlspecialchars($vm->user->FirstName) ?> <?= htmlspecialchars($vm->user->LastName) ?></span>?
                </p>
                <p class="text-muted small">This action is permanent and cannot be reversed.</p>
            </div>

            <form method="post" action="/deleteUser">
                <input type="hidden" name="UserId" value="<?= (int)$vm->user->UserId ?>">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                
                <div class="d-flex justify-content-between mt-3">
                    <a href="/users" class="btn btn-outline-secondary px-4">Keep User</a>
                    <button type="submit" class="btn btn-primary px-4">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>