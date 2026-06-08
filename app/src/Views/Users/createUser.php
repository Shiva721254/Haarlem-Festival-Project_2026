<?php
/** @var ManageUserViewModel $vm */
$title = "Create New User"; // Set the title for the header partial
require __DIR__ . "/../Partials/header.php"; 
use App\Middleware\AuthMiddleware;
?>
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto card-form">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create New User</h4>
            </div>
            <div class="card-body">

                <?php if (isset($error)): ?>
                    <div class="error-box">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/saveUser" id="create-user-form">
                    <input type="hidden" name="UserId" value="0">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="FirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="FirstName" name="FirstName" 
                                value="<?= htmlspecialchars($vm->user->FirstName ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="LastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="LastName" name="LastName" 
                                value="<?= htmlspecialchars($vm->user->LastName ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="Email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="Email" name="Email" 
                                value="<?= htmlspecialchars($vm->user->Email ?? '') ?>" required>
                        </div>

                        <?php // PASSWORD ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" name="Password" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                
                                <div id="password-requirements" class="mt-2 small border rounded p-2 bg-light">
                                    <p class="mb-1 fw-bold">Password must contain:</p>
                                    <ul class="list-unstyled mb-0">
                                        <li id="req-length" class="text-muted"><i class="bi bi-circle"></i> At least 8 characters</li>
                                        <li id="req-capital" class="text-muted"><i class="bi bi-circle"></i> A capital letter</li>
                                        <li id="req-number" class="text-muted"><i class="bi bi-circle"></i> A number</li>
                                        <li id="req-symbol" class="text-muted"><i class="bi bi-circle"></i> A special symbol</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
                                <input type="password" id="confirm_password" name="PasswordConfirm" class="form-control" required>
                                <div id="match-error" class="small mt-2 text-danger d-none">
                                    <i class="bi bi-exclamation-triangle"></i> Passwords do not match.
                                </div>
                                <div id="match-success" class="small mt-2 text-success d-none">
                                    <i class="bi bi-check-circle"></i> Passwords match!
                                </div>
                            </div>
                        </div>

                        <?php // ENUMERATIONS ?>
                        <?php if (isset($_SESSION['Role']) && $_SESSION['Role']->value === 'admin'): ?>
                            <div class="col-md-6">
                                <label for="Role" class="form-label">Role</label>
                                <select class="form-select" id="Role" name="Role" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <?php foreach (\App\Enums\UserRole::cases() as $role): ?>
                                        <option value="<?= $role->value ?>"><?= $role->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="/users" class="btn btn-outline-secondary">Cancel</a>

                        <button 
                            type="submit" 
                            id="submitBtn" 
                            class="btn btn-primary px-4 g-recaptcha"
                            data-sitekey="6LcRjF8sAAAAAA8Yhjp-lQFxIpea53uZMsYTbMSR"
                            data-callback='onSubmit'
                            data-action='submit' 
                            disabled>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://www.google.com/recaptcha/api.js"></script>

<script>
   function onSubmit(token) {
        const form = document.getElementById("create-user-form");

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'g-recaptcha-response';
        input.value = token;

        form.appendChild(input);
        form.submit();
    }
</script>
<script src="/assets/js/password.js" defer></script>
<?php require __DIR__ . "/../Partials/footer.php"; ?>
