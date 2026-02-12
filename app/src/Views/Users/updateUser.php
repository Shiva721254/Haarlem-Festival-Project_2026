<?php
/** @var ManageUserViewModel $vm */
$user = $vm->user;
$title = "Update User: " . htmlspecialchars($user->FirstName); 
require __DIR__ . "/../Partials/header.php"; 
?>
<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Edit User Details</h4>
        </div>
        <div class="card-body">
            <form method="post" action="/saveUser" id="demo-form">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\AuthMiddleware::generateCsrfToken(); ?>">
                <input type="hidden" name="UserId" value="<?= htmlspecialchars($user->UserId) ?>">

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
                    <div class="col-md-6">
                        <label for="Role" class="form-label">Role</label>
                        <select class="form-select" id="Role" name="Role" required>
                            <?php foreach (\App\Enums\UserRole::cases() as $role): ?>
                                <option value="<?= $role->value ?>" <?= $user->Role === $role ? 'selected' : '' ?>>
                                    <?= $role->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>                      
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="/users" class="btn btn-outline-secondary">Back to List</a>
                    <button 
                        type="submit" 
                        id="submitBtn" 
                        class="btn btn-primary px-4 g-recaptcha"
                        data-sitekey="6LcRjF8sAAAAAA8Yhjp-lQFxIpea53uZMsYTbMSR"
                        data-callback='onSubmit'
                        data-action='submit' 
                        disabled>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js"></script>

<script>
   function onSubmit(token) {
        const form = document.getElementById("demo-form");

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'g-recaptcha-response';
        input.value = token;

        form.appendChild(input);
        form.submit();
    }
</script>
<?php require __DIR__ . "/../Partials/footer.php"; ?>