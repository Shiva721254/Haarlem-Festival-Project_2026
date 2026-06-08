<?php require __DIR__ . "/../Partials/header.php"; ?>

<div class="container mt-5">
    <div class="card shadow-sm mx-auto card-narrow">
        <div class="card-header text-white text-center panel-header-purple">
            <h4 class="mb-0">Haarlem Festival Login</h4>
        </div>
        <div class="card-body">
            <?php if ($vm->error): ?>
                <div class="alert alert-danger p-2 small"><?= $vm->error ?></div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="Email" class="form-control" value="<?= htmlspecialchars($vm->email) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="Password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-purple w-100">Sign In</button>
                <div class="text-end mt-1">
                    <a href="/forgotPassword" class="small text-decoration-none text-purple">Forgot password?</a>
                </div>
                <div>
                    <h5 class="mt-3">Don't have an account?</h5>
                </div>
                <div>
                    <a href="/createUser" class="btn btn-success w-100 text-white">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () 
{
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    // 1. Toggle Visibility Logic
    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        
        passwordInput.setAttribute('type', type);
        
        // Toggle Icon
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
});
</script>

<?php require __DIR__ . "/../Partials/footer.php"; ?>
