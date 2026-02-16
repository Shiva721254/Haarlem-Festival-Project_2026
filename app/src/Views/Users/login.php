<?php 
$title = "Login";
require __DIR__ . "/../Partials/header.php"; 
?>
<link rel="stylesheet" href="/assets/css/userCRUD.css">
<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 450px;">
        <div class="card-header text-center py-3">
            <h4 class="mb-0">Jazz Festival Login</h4>
        </div>
        <div class="card-body p-4">
            
            <?php if ($vm->error): ?>
                <div class="error-message mb-3 small">
                    <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($vm->error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                
                <div class="mb-4">
                    <label class="filter-label">Email Address</label>
                    <input type="email" name="Email" class="form-control py-2" 
                           value="<?= htmlspecialchars($vm->email) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="filter-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="Password" class="form-control py-2" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <a href="/forgotPassword" class="small text-decoration-none" style="color: var(--jazz-purple);">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mb-4 shadow-sm">
                    Sign In
                </button>

                <div class="border-top pt-4 text-center">
                    <p class="small text-muted mb-3">Don't have a festival account yet?</p>
                    <a href="/createUser" class="btn btn-success w-100 py-2 shadow-sm">
                        Create New Account
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle Icon between eye and eye-slash
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });
});
</script>

<?php require __DIR__ . "/../Partials/footer.php"; ?>