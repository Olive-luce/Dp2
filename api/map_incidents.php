<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

header('Content-Type: application/json');

requireApiAuth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($method === 'GET') {
    echo json_encode(fetchIncidents($pdo, ['mapped' => true]));
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $latitude = $payload['latitude'] ?? null;
    $longitude = $payload['longitude'] ?? null;
    $incidentType = trim($payload['incident_type'] ?? '');
    $description = trim($payload['description'] ?? '');

    if ($latitude === null || $latitude === '' || $longitude === null || $longitude === '' || !$incidentType || !$description) {
        echo json_encode(['success' => false, 'message' => 'Location, incident type, and description are required']);
        exit;
    }

    $address = trim($payload['address'] ?? '');
    $incidentId = createIncident($pdo, [
        'title' => $incidentType . ' reported' . ($address ? ' near ' . $address : ''),
        'description' => $description,
        'incident_type' => $incidentType,
        'severity' => $payload['severity'] ?? 'medium',
        'priority' => $payload['severity'] ?? 'medium',
        'status' => 'reported',
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $address ?: null,
    ], $userId);

    echo json_encode(['success' => true, 'id' => $incidentId]);
    exit;
}

if ($method === 'PUT') {
    requireApiRole(['admin', 'responder']);

    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($payload['id'] ?? 0);
    $status = $payload['status'] ?? null;

    if (!$id || $status === null) {
        echo json_encode(['success' => false, 'message' => 'Missing id or status']);
        exit;
    }

    echo json_encode(['success' => setIncidentStatus($pdo, $id, (string) $status)]);
    exit;
}

if ($method === 'DELETE') {
    requireApiRole(['admin']);

    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing id']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM disaster_incidents WHERE id = ?');
    echo json_encode(['success' => $stmt->execute([$id])]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
