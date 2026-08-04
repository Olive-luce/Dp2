<?php
$role = $_SESSION['role'] ?? 'citizen';
$displayName = $_SESSION['full_name'] ?? 'Guest';
?>
<nav class="navbar" aria-label="Top navigation">
    <div class="navbar-inner">
        <div class="d-flex align-items-center gap-2">
            <button class="nav-control sidebar-toggle" id="sidebarToggle" type="button" aria-expanded="false" aria-controls="sidebarMenu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">
                <span class="brand-badge"><i class="fa-solid fa-shield-halved"></i></span>
                <span class="brand-title">Government Emergency Operations Center</span>
            </a>
        </div>
        <div class="navbar-actions">
            <button class="nav-action" id="themeToggle" type="button">
                <i class="fa-solid fa-moon me-2"></i><span>Dark mode</span>
            </button>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="nav-action"><i class="fa-solid fa-user-shield me-2"></i><?php echo htmlspecialchars($displayName); ?></span>
                <a class="nav-action" href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i><span>Logout</span></a>
            <?php else: ?>
                <a class="nav-action" href="<?php echo BASE_URL; ?>/auth/login.php"><i class="fa-solid fa-right-to-bracket me-2"></i><span>Login</span></a>
            <?php endif; ?>
        </div>
    </div>
</nav>
