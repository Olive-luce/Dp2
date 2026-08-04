<?php
require_once __DIR__ . '/../config/dbconnection.php';
header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, latitude, longitude, incident_type, severity, description, created_at, reporter FROM incident_map_reports ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $latitude = $payload['latitude'] ?? null;
    $longitude = $payload['longitude'] ?? null;
    $incidentType = $payload['incident_type'] ?? null;
    $severity = $payload['severity'] ?? null;
    $description = $payload['description'] ?? null;
    $reporter = $payload['reporter'] ?? 'Anonymous';
    $address = $payload['address'] ?? null;
    $status = $payload['status'] ?? 'reported';

    if ($latitude === null || $longitude === null || $incidentType === null || $severity === null || $description === null) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO incident_map_reports (latitude, longitude, incident_type, severity, description, address, reporter, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$latitude, $longitude, $incidentType, $severity, $description, $address, $reporter, $status]);

    echo json_encode(['success' => $success, 'id' => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id === null) {
        echo json_encode(['success' => false, 'message' => 'Missing id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM incident_map_reports WHERE id = ?');
    $success = $stmt->execute([$id]);
    echo json_encode(['success' => $success]);
    exit;
}

if ($method === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = $payload['id'] ?? null;
    $status = $payload['status'] ?? null;
    if ($id === null || $status === null) {
        echo json_encode(['success' => false, 'message' => 'Missing id or status']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE incident_map_reports SET status = ? WHERE id = ?');
    $success = $stmt->execute([$status, $id]);
    echo json_encode(['success' => $success]);
    exit;
}
