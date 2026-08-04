<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireGuest();
$pageTitle = 'Reset Password';
$pageSubtitle = 'Recover access to your account.';

$skipPageHeader = true;

include_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
    <div class="glass-card p-4 p-lg-5">
        <h2 class="fw-bold mb-3">Recover Access</h2>
        <p class="text-muted mb-4">Reset instructions will be sent to the registered email address.</p>
        <div class="alert alert-secondary">Please contact the system administrator to reset your password.</div>
        <a class="btn btn-outline-primary" href="login.php">Back to login</a>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
