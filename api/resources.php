<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$role = $_SESSION['role'] ?? 'citizen';

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, name, category, quantity, unit, location, status FROM resources ORDER BY id DESC');
    $resources = $stmt->fetchAll();

    $allocStmt = $pdo->query('SELECT resource_id, SUM(quantity) AS distributed FROM resource_allocations GROUP BY resource_id');
    $allocations = $allocStmt->fetchAll(PDO::FETCH_ASSOC);
    $allocationMap = [];
    foreach ($allocations as $row) {
        $allocationMap[(int)$row['resource_id']] = (int)$row['distributed'];
    }

    foreach ($resources as &$resource) {
        $resource['distributed'] = $allocationMap[(int)$resource['id']] ?? 0;
        $resource['remaining'] = (int)$resource['quantity'] - $resource['distributed'];
    }

    echo json_encode($resources);
    exit;
}

if ($method === 'POST') {
    if ($role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admins can manage resources']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $name = trim($payload['name'] ?? '');
    $category = trim($payload['category'] ?? '');
    $quantity = (int)($payload['quantity'] ?? 0);
    $unit = trim($payload['unit'] ?? '');
    $location = trim($payload['location'] ?? '');
    $status = trim($payload['status'] ?? 'available');

    if (!$name || !$category || !$location) {
        echo json_encode(['success' => false, 'message' => 'Resource name, category, and location are required']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO resources (name, category, quantity, unit, location, status) VALUES (?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$name, $category, $quantity, $unit, $location, $status]);
    echo json_encode(['success' => $success, 'id' => $pdo->lastInsertId()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
