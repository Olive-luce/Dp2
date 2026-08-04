<?php
require_once __DIR__ . '/../config/dbconnection.php';

const INCIDENT_SEVERITIES = ['low', 'medium', 'high', 'critical'];
const INCIDENT_PRIORITIES = ['low', 'medium', 'high', 'critical'];
const INCIDENT_STATUSES = ['reported', 'acknowledged', 'in_progress', 'resolved'];

function normalizeIncidentValue(string $value, array $allowed, string $fallback): string
{
    $value = strtolower(trim($value));
    return in_array($value, $allowed, true) ? $value : $fallback;
}

/**
 * Single source of truth for incident listings used by the admin table, the
 * responder dispatch board, the interactive map, and the public feed.
 */
function fetchIncidents(PDO $pdo, array $filters = []): array
{
    $sql = 'SELECT i.id, i.title, i.description, i.incident_type, i.severity, i.priority, i.status,
                   i.latitude, i.longitude, i.address, i.created_at, i.updated_at,
                   i.reported_by, i.assigned_to,
                   u.full_name AS reporter, a.full_name AS assigned_responder
            FROM disaster_incidents i
            LEFT JOIN users u ON u.id = i.reported_by
            LEFT JOIN users a ON a.id = i.assigned_to';

    $where = [];
    $params = [];

    if (!empty($filters['mapped'])) {
        $where[] = 'i.latitude IS NOT NULL AND i.longitude IS NOT NULL';
    }
    if (!empty($filters['status'])) {
        $where[] = 'i.status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['open'])) {
        $where[] = "i.status <> 'resolved'";
    }
    if (!empty($filters['assigned_to'])) {
        $where[] = 'i.assigned_to = ?';
        $params[] = $filters['assigned_to'];
    }
    if (!empty($filters['reported_by'])) {
        $where[] = 'i.reported_by = ?';
        $params[] = $filters['reported_by'];
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY i.created_at DESC';
    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function fetchIncidentUpdates(PDO $pdo, int $incidentId): array
{
    $stmt = $pdo->prepare('SELECT update_text, status, created_at FROM incident_updates WHERE incident_id = ? ORDER BY created_at DESC, id DESC');
    $stmt->execute([$incidentId]);

    return $stmt->fetchAll();
}

function logIncidentUpdate(PDO $pdo, int $incidentId, string $text, string $status): void
{
    $stmt = $pdo->prepare('INSERT INTO incident_updates (incident_id, update_text, status) VALUES (?, ?, ?)');
    $stmt->execute([$incidentId, $text, $status]);
}

/**
 * Notifies the reporter and the assigned responder so status changes reach the
 * people involved instead of only living in the incidents table.
 */
function notifyIncidentParticipants(PDO $pdo, int $incidentId, string $message): void
{
    $stmt = $pdo->prepare('SELECT reported_by, assigned_to FROM disaster_incidents WHERE id = ?');
    $stmt->execute([$incidentId]);
    $incident = $stmt->fetch();

    if (!$incident) {
        return;
    }

    $recipients = array_unique(array_filter([
        (int) $incident['reported_by'],
        (int) ($incident['assigned_to'] ?? 0),
    ]));

    if (!$recipients) {
        return;
    }

    $insert = $pdo->prepare('INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, "incident", "unread")');
    foreach ($recipients as $recipientId) {
        $insert->execute([$recipientId, $message]);
    }
}

function createIncident(PDO $pdo, array $data, int $reportedBy): int
{
    $stmt = $pdo->prepare('INSERT INTO disaster_incidents (title, description, incident_type, severity, priority, status, latitude, longitude, address, reported_by, assigned_to)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $data['title'],
        $data['description'],
        $data['incident_type'],
        normalizeIncidentValue($data['severity'] ?? '', INCIDENT_SEVERITIES, 'medium'),
        normalizeIncidentValue($data['priority'] ?? '', INCIDENT_PRIORITIES, 'medium'),
        normalizeIncidentValue($data['status'] ?? '', INCIDENT_STATUSES, 'reported'),
        $data['latitude'] !== '' && $data['latitude'] !== null ? $data['latitude'] : null,
        $data['longitude'] !== '' && $data['longitude'] !== null ? $data['longitude'] : null,
        $data['address'] ?? null,
        $reportedBy,
        !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
    ]);

    $incidentId = (int) $pdo->lastInsertId();
    logIncidentUpdate($pdo, $incidentId, 'Incident reported.', 'reported');
    notifyIncidentParticipants($pdo, $incidentId, 'Incident logged: ' . $data['title']);

    return $incidentId;
}

function setIncidentStatus(PDO $pdo, int $incidentId, string $status, string $note = ''): bool
{
    $status = normalizeIncidentValue($status, INCIDENT_STATUSES, 'reported');

    $stmt = $pdo->prepare('UPDATE disaster_incidents SET status = ? WHERE id = ?');
    $success = $stmt->execute([$status, $incidentId]);

    if ($success) {
        logIncidentUpdate($pdo, $incidentId, $note ?: 'Status changed to ' . $status . '.', $status);
        notifyIncidentParticipants($pdo, $incidentId, 'Incident #' . $incidentId . ' is now ' . str_replace('_', ' ', $status) . '.');
    }

    return $success;
}
