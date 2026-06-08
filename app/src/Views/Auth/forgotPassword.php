<?php 
require __DIR__ . "/../Partials/header.php"; 
$title = "forgot password";
?>

<div class="container mt-5">
    <div class="card shadow-sm mx-auto card-auth">
        <div class="card-header text-white text-center panel-header-purple">
            <h4 class="mb-0">Reset Your Password</h4>
        </div>
        <div class="card-body">
            <p class="text-muted small">Enter your email address and we will send you a link to reset your password.</p>
            
            <form action="/send-reset-link" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="Email" class="form-control" placeholder="name@example.com" required>
                </div>
                <button type="submit" class="btn btn-purple w-100">
                    Send Reset Link
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="/login" class="small text-decoration-none text-muted">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../Partials/footer.php"; ?>
