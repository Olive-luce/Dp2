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

/**
 * JSON equivalents of requireAuth() for the API endpoints, which must answer
 * with a status code and JSON body rather than redirecting to the login page.
 */
function requireApiAuth(): void {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
}

function requireApiRole(array $allowedRoles): void {
    requireApiAuth();

    if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have access to this action']);
        exit;
    }
}

function requireGuest(): void {
    if (isAuthenticated()) {
        redirectToRoleDashboard($_SESSION['role'] ?? 'citizen');
    }
}
