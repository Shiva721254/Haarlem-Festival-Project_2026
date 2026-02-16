<?php
/** @var ManageUserViewModel $vm */
$title = "Create New User"; 
require __DIR__ . "/../Partials/header.php"; 
?>
<link rel="stylesheet" href="/assets/css/userCRUD.css">
<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header">
            <h4 class="mb-0">Create New User</h4>
        </div>
        <div class="card-body">

            <?php if (isset($error)): ?>
                <div class="error-message mb-3">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/saveUser" id="captcha-form">
                <input type="hidden" name="UserId" value="0">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="FirstName" class="form-label small fw-bold">First Name</label>
                        <input type="text" class="form-control" id="FirstName" name="FirstName" 
                               value="<?= htmlspecialchars($vm->user->FirstName ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="LastName" class="form-label small fw-bold">Last Name</label>
                        <input type="text" class="form-control" id="LastName" name="LastName" 
                               value="<?= htmlspecialchars($vm->user->LastName ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="Email" class="form-label small fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="Email" name="Email" 
                               value="<?= htmlspecialchars($vm->user->Email ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="Password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        
                        <div id="password-requirements" class="mt-2 small border rounded p-2 bg-light">
                            <p class="mb-1 fw-bold">Must contain:</p>
                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                <li id="req-length" class="text-muted"><i class="bi bi-circle"></i> 8+ characters</li>
                                <li id="req-capital" class="text-muted"><i class="bi bi-circle"></i> Capital letter</li>
                                <li id="req-number" class="text-muted"><i class="bi bi-circle"></i> A number</li>
                                <li id="req-symbol" class="text-muted"><i class="bi bi-circle"></i> Special symbol</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label small fw-bold">Confirm Password</label>
                        <input type="password" id="confirm_password" name="PasswordConfirm" class="form-control" required>
                        <div id="match-error" class="small mt-2 text-danger d-none">
                            <i class="bi bi-exclamation-triangle"></i> Passwords do not match.
                        </div>
                        <div id="match-success" class="small mt-2 text-success d-none">
                            <i class="bi bi-check-circle"></i> Passwords match!
                        </div>
                    </div>

                    <?php if (isset($_SESSION['Role']) && $_SESSION['Role']->value === 'admin'): ?>
                        <div class="col-12">
                            <label for="Role" class="form-label small fw-bold">Assign Role</label>
                            <select class="form-select" id="Role" name="Role" required>
                                <option value="" selected disabled>Choose...</option>
                                <?php foreach (\App\Enums\UserRole::cases() as $role): ?>
                                    <option value="<?= $role->value ?>"><?= $role->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 d-flex justify-content-between border-top pt-3">
                    <a href="/users" class="btn btn-outline-secondary px-4">Cancel</a>

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
        const form = document.getElementById("captcha-form");
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