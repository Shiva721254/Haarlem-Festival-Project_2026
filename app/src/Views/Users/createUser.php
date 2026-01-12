<?php
/** @var ManageUserViewModel $vm */
$title = "Create New User"; // Set the title for the header partial
require __DIR__ . "/../Partials/header.php"; 
?>
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create New User</h4>
            </div>
            <div class="card-body">
                <form method="post" action="/saveUser">
                    <input type="hidden" name="UserId" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="FirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="FirstName" name="FirstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="LastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="LastName" name="LastName" required>
                        </div>
                        <div class="col-12">
                            <label for="Email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="Email" name="Email" required>
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

                        <div class="col-md-6">
                            <label for="Address" class="form-label">Region/Address</label>
                            <select class="form-select" id="Address" name="Address" required>
                                <option value="" selected disabled>Choose...</option>
                                <?php foreach (\App\Enums\Address::cases() as $address): ?>
                                    <option value="<?= $address->value ?>"><?= $address->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="/users" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" id="submitBtn" class="btn btn-primary px-4" disabled>Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const toggleBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    const requirements = {
        length: { el: document.getElementById('req-length'), regex: /.{8,}/ },
        capital: { el: document.getElementById('req-capital'), regex: /[A-Z]/ },
        number: { el: document.getElementById('req-number'), regex: /[0-9]/ },
        symbol: { el: document.getElementById('req-symbol'), regex: /[^A-Za-z0-9]/ }
    };

    // 1. Toggle Visibility Logic
    toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        confirmInput.setAttribute('type', type); // Sync both
        
        // Toggle Icon
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
    });

    // 2. Main Validation Function
    function validateForm() {
        const val = passwordInput.value;
        const confirmVal = confirmInput.value;
        let strengthValid = true;

        // Check Strength
        for (const key in requirements) {
            const isValid = requirements[key].regex.test(val);
            const element = requirements[key].el;
            const icon = element.querySelector('i');

            if (isValid) {
                element.classList.remove('text-muted');
                element.classList.add('text-success', 'fw-bold');
                icon.classList.replace('bi-circle', 'bi-check-circle-fill');
            } else {
                element.classList.remove('text-success', 'fw-bold');
                element.classList.add('text-muted');
                icon.classList.replace('bi-check-circle-fill', 'bi-circle');
                strengthValid = false;
            }
        }

        // Check Matching
        const matchError = document.getElementById('match-error');
        const matchSuccess = document.getElementById('match-success');
        let passwordsMatch = val === confirmVal && val !== '';

        if (confirmVal !== '') {
            if (passwordsMatch) {
                matchError.classList.add('d-none');
                matchSuccess.classList.remove('d-none');
                confirmInput.classList.add('is-valid');
                confirmInput.classList.remove('is-invalid');
            } else {
                matchError.classList.remove('d-none');
                matchSuccess.classList.add('d-none');
                confirmInput.classList.add('is-invalid');
                confirmInput.classList.remove('is-valid');
            }
        }

        // Final Button State
        submitBtn.disabled = !(strengthValid && passwordsMatch);
    }

    passwordInput.addEventListener('input', validateForm);
    confirmInput.addEventListener('input', validateForm);
});
</script>
<?php require __DIR__ . "/../Partials/footer.php"; ?>