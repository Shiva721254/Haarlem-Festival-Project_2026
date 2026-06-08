<?php
require __DIR__ . "/../Partials/header.php"; 
$title = "reset password";
?>
<div class="container mt-5">
    <div class="card shadow-sm mx-auto card-wide-form">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Reset Your Password</h4>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">Please enter and confirm your new secure password below.</p>

            <form action="/update-password" method="POST" id="reset-password-form">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold">New Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="Password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        
                        <div id="password-requirements" class="mt-3 small border rounded p-3 bg-light">
                            <p class="mb-2 fw-bold text-secondary border-bottom pb-1">Security Requirements:</p>
                            <ul class="list-unstyled mb-0">
                                <li id="req-length" class="text-muted mb-1"><i class="bi bi-circle me-2"></i>At least 8 characters</li>
                                <li id="req-capital" class="text-muted mb-1"><i class="bi bi-circle me-2"></i>A capital letter</li>
                                <li id="req-number" class="text-muted mb-1"><i class="bi bi-circle me-2"></i>A number</li>
                                <li id="req-symbol" class="text-muted"><i class="bi bi-circle me-2"></i>A special symbol</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="PasswordConfirm" class="form-control" required>
                        
                        <div id="match-error" class="alert alert-danger d-none mt-3 p-2 small">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Passwords do not match.
                        </div>
                        <div id="match-success" class="alert alert-success d-none mt-3 p-2 small">
                            <i class="bi bi-check-circle-fill me-2"></i>Passwords match!
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/login" class="btn btn-link text-decoration-none me-md-2">Back to Login</a>
                    <button type="submit" 
                        id="submitBtn" 
                        class="btn btn-primary px-5 g-recaptcha"
                        data-sitekey="6LcRjF8sAAAAAA8Yhjp-lQFxIpea53uZMsYTbMSR"
                        data-callback='onSubmit'
                        data-action='submit'
                        disabled>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/password.js" defer></script>
<script src="https://www.google.com/recaptcha/api.js"></script>

<script>
   function onSubmit(token) {
        const form = document.getElementById("reset-password-form");

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'g-recaptcha-response';
        input.value = token;

        form.appendChild(input);
        form.submit();
    }
</script>
<?php require __DIR__ . "/../Partials/footer.php"; ?>
