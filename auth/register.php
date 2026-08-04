<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireGuest();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleName = $_POST['role'] ?? 'citizen';
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $message = 'That username or email already exists.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, full_name, email, password_hash, phone, city, status) VALUES (?, ?, ?, ?, ?, ?, "active")');
        $stmt->execute([$username, $fullName, $email, $hash, $phone, $city]);
        $userId = $pdo->lastInsertId();

        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
        $roleStmt->execute([$roleName]);
        $role = $roleStmt->fetch();

        if ($role) {
            $assignStmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id, status) VALUES (?, ?, "active")');
            $assignStmt->execute([$userId, $role['id']]);
        }

        $message = 'Account created successfully. Please login.';
    }
}

include_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
    <div class="glass-card p-4 p-lg-5">
        <h2 class="fw-bold mb-3">Create Account</h2>
        <p class="text-muted mb-4">Join the community disaster response network.</p>
        <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone">
            </div>
            <div class="mb-3">
                <label class="form-label">City</label>
                <input type="text" class="form-control" name="city">
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role">
                    <option value="citizen">Citizen</option>
                    <option value="volunteer">Volunteer</option>
                    <option value="responder">Responder</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100">Register</button>
        </form>
        <div class="mt-3 text-center">
            <a href="login.php">Already have an account?</a>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
