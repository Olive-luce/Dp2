<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

header('Content-Type: application/json');

requireApiAuth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($method === 'GET') {
    echo json_encode(fetchIncidents($pdo));
    exit;
}

if ($method === 'POST') {
    requireApiRole(['admin', 'responder', 'citizen', 'volunteer']);

    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $title = trim($payload['title'] ?? '');
    $description = trim($payload['description'] ?? '');
    $incidentType = trim($payload['incident_type'] ?? '');

    if (!$title || !$description || !$incidentType) {
        echo json_encode(['success' => false, 'message' => 'Title, description, and incident type are required']);
        exit;
    }

    $reportedBy = $userId;
    $reporter = trim($payload['reporter'] ?? '');
    if ($reporter && ($_SESSION['role'] ?? '') === 'admin') {
        $userStmt = $pdo->prepare('SELECT id FROM users WHERE full_name = ? OR username = ? LIMIT 1');
        $userStmt->execute([$reporter, $reporter]);
        $reportedBy = (int) ($userStmt->fetchColumn() ?: $userId);
    }

    $assignedTo = null;
    $assignedName = trim($payload['assigned_to'] ?? '');
    if ($assignedName !== '') {
        $responderStmt = $pdo->prepare('SELECT id FROM users WHERE full_name = ? OR username = ? LIMIT 1');
        $responderStmt->execute([$assignedName, $assignedName]);
        $assignedTo = $responderStmt->fetchColumn() ?: null;
    }

    $incidentId = createIncident($pdo, [
        'title' => $title,
        'description' => $description,
        'incident_type' => $incidentType,
        'severity' => $payload['severity'] ?? 'medium',
        'priority' => $payload['priority'] ?? 'medium',
        'status' => $payload['status'] ?? 'reported',
        'latitude' => $payload['latitude'] ?? null,
        'longitude' => $payload['longitude'] ?? null,
        'address' => trim($payload['address'] ?? '') ?: null,
        'assigned_to' => $assignedTo,
    ], $reportedBy);

    echo json_encode(['success' => true, 'id' => $incidentId]);
    exit;
}

if ($method === 'PUT') {
    requireApiRole(['admin', 'responder']);

    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($payload['id'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing incident id']);
        exit;
    }

    if (isset($payload['status'])) {
        setIncidentStatus($pdo, $id, (string) $payload['status'], trim($payload['note'] ?? ''));
    }

    $fields = [];
    $values = [];
    if (isset($payload['severity'])) {
        $fields[] = 'severity = ?';
        $values[] = normalizeIncidentValue((string) $payload['severity'], INCIDENT_SEVERITIES, 'medium');
    }
    if (isset($payload['priority'])) {
        $fields[] = 'priority = ?';
        $values[] = normalizeIncidentValue((string) $payload['priority'], INCIDENT_PRIORITIES, 'medium');
    }
    if (array_key_exists('assigned_to', $payload)) {
        $fields[] = 'assigned_to = ?';
        $values[] = $payload['assigned_to'] !== '' ? (int) $payload['assigned_to'] : null;
    }
    if (isset($payload['description'])) {
        $fields[] = 'description = ?';
        $values[] = trim((string) $payload['description']);
    }

    if ($fields) {
        $values[] = $id;
        $stmt = $pdo->prepare('UPDATE disaster_incidents SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($values);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    requireApiRole(['admin']);

    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing incident id']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM disaster_incidents WHERE id = ?');
    echo json_encode(['success' => $stmt->execute([$id])]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
