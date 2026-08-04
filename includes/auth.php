<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated(): bool {
    return !empty($_SESSION['user_id']);
}

function redirectToRoleDashboard(string $role): void {
    $map = [
        'admin' => '/admin/dashboard.php',
        'responder' => '/responder/dashboard.php',
        'volunteer' => '/volunteer/dashboard.php',
        'citizen' => '/citizen/dashboard.php',
    ];

    $path = $map[$role] ?? '/index.php';
    header('Location: ' . BASE_URL . $path);
    exit;
}

function requireAuth(array $allowedRoles = []): void {
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }

    if ($allowedRoles && !in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
        redirectToRoleDashboard($_SESSION['role'] ?? 'citizen');
    }
}

function requireGuest(): void {
    if (isAuthenticated()) {
        redirectToRoleDashboard($_SESSION['role'] ?? 'citizen');
    }
}
