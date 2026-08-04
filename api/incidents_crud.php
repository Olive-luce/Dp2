<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT i.id, i.title, i.description, i.incident_type, i.severity, i.status, i.latitude, i.longitude, i.created_at, u.full_name AS reporter, a.full_name AS assigned_responder FROM disaster_incidents i LEFT JOIN users u ON u.id = i.reported_by LEFT JOIN users a ON a.id = i.assigned_to ORDER BY i.created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $title = trim($payload['title'] ?? '');
    $description = trim($payload['description'] ?? '');
    $incidentType = trim($payload['incident_type'] ?? '');
    $severity = trim($payload['severity'] ?? 'Medium');
    $priority = trim($payload['priority'] ?? 'Medium');
    $status = trim($payload['status'] ?? 'reported');
    $priority = $priority ?: 'Medium';
    $latitude = trim($payload['latitude'] ?? '');
    $longitude = trim($payload['longitude'] ?? '');
    $assignedTo = trim($payload['assigned_to'] ?? '');
    $reporter = trim($payload['reporter'] ?? '');
    $photo = $payload['photo'] ?? '';

    if (!$title || !$description || !$incidentType || !$reporter) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $userStmt = $pdo->prepare('SELECT id FROM users WHERE full_name = ? OR username = ? LIMIT 1');
    $userStmt->execute([$reporter, $reporter]);
    $user = $userStmt->fetch();
    $reportedBy = $user['id'] ?? 1;

    $stmt = $pdo->prepare('INSERT INTO disaster_incidents (title, description, incident_type, severity, status, latitude, longitude, reported_by, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$title, $description, $incidentType, $severity, $status, $latitude ?: 0, $longitude ?: 0, $reportedBy, $assignedTo ?: null]);

    echo json_encode(['success' => $success, 'id' => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = $payload['id'] ?? null;
    $status = $payload['status'] ?? null;
    $severity = $payload['severity'] ?? null;
    $priority = $payload['priority'] ?? null;
    $assignedTo = $payload['assigned_to'] ?? null;
    $description = $payload['description'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing incident id']);
        exit;
    }

    $fields = [];
    $values = [];
    if ($status !== null) { $fields[] = 'status = ?'; $values[] = $status; }
    if ($severity !== null) { $fields[] = 'severity = ?'; $values[] = $severity; }
    if ($priority !== null) { $fields[] = 'priority = ?'; $values[] = $priority; }
    if ($assignedTo !== null) { $fields[] = 'assigned_to = ?'; $values[] = $assignedTo; }
    if ($description !== null) { $fields[] = 'description = ?'; $values[] = $description; }
    $values[] = $id;

    $stmt = $pdo->prepare('UPDATE disaster_incidents SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $success = $stmt->execute($values);
    echo json_encode(['success' => $success]);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing incident id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM disaster_incidents WHERE id = ?');
    $success = $stmt->execute([$id]);
    echo json_encode(['success' => $success]);
    exit;
}
