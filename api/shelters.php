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
    $stmt = $pdo->query('SELECT id, name, address, capacity, current_occupancy, contact_person, status FROM shelters ORDER BY id DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    if ($role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admins can manage shelters']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $name = trim($payload['name'] ?? '');
    $address = trim($payload['address'] ?? '');
    $capacity = (int)($payload['capacity'] ?? 0);
    $occupancy = (int)($payload['current_occupancy'] ?? 0);
    $contact = trim($payload['contact_person'] ?? '');
    $status = trim($payload['status'] ?? 'available');

    if (!$name || !$address) {
        echo json_encode(['success' => false, 'message' => 'Shelter name and location are required']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO shelters (name, address, capacity, current_occupancy, contact_person, status) VALUES (?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$name, $address, $capacity, $occupancy, $contact, $status]);
    echo json_encode(['success' => $success, 'id' => $pdo->lastInsertId()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
