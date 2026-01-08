<?php
require __DIR__ . "/../Partials/header.php"; 
$title = "reset password";
?>
<form action="/update-password" method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
    
    <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="Password" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label>Confirm New Password</label>
        <input type="password" name="PasswordConfirm" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Update Password</button>
</form>

<?php require __DIR__ . "/../Partials/footer.php"; ?>