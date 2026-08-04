<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireGuest();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $roleStmt = $pdo->prepare('SELECT r.name FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND ur.status = "active" LIMIT 1');
        $roleStmt->execute([$user['id']]);
        $roleRow = $roleStmt->fetch();
        $role = $roleRow['name'] ?? 'citizen';

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $role;
        $_SESSION['email'] = $user['email'];

        redirectToRoleDashboard($role);
    }

    $message = 'Invalid credentials. Please try again.';
}

include_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
    <div class="glass-card p-4 p-lg-5">
        <h2 class="fw-bold mb-3">Welcome Back</h2>
        <p class="text-muted mb-4">Sign in to access your disaster coordination dashboard.</p>
        <?php if ($message): ?><div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 d-flex justify-content-between text-sm">
            <a href="register.php">Create an account</a>
            <a href="forgot_password.php">Forgot password?</a>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
