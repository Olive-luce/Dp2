<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['volunteer']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$volunteerStmt = $pdo->prepare('SELECT id, availability FROM volunteers WHERE user_id = ? LIMIT 1');
$volunteerStmt->execute([$userId]);
$volunteer = $volunteerStmt->fetch();
$volunteerId = (int) ($volunteer['id'] ?? 0);

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $volunteerId) {
    $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($assignmentId && in_array($status, ['accepted', 'in_progress', 'completed'], true)) {
        $stmt = $pdo->prepare('UPDATE volunteer_assignments SET status = ? WHERE id = ? AND volunteer_id = ?');
        $stmt->execute([$status, $assignmentId, $volunteerId]);

        $incidentStmt = $pdo->prepare('SELECT incident_id FROM volunteer_assignments WHERE id = ? AND volunteer_id = ?');
        $incidentStmt->execute([$assignmentId, $volunteerId]);
        $incidentId = (int) $incidentStmt->fetchColumn();

        if ($incidentId) {
            $label = ($_SESSION['full_name'] ?? 'A volunteer') . ' marked their task as ' . str_replace('_', ' ', $status) . '.';
            logIncidentUpdate($pdo, $incidentId, $label, $status === 'completed' ? 'in_progress' : 'in_progress');
            notifyIncidentParticipants($pdo, $incidentId, $label);
        }

        $notice = 'Task updated.';
    }
}

$assignments = [];
if ($volunteerId) {
    $stmt = $pdo->prepare('SELECT va.id, va.status, va.assignment_note, va.created_at,
                                  i.id AS incident_id, i.title, i.incident_type, i.severity, i.status AS incident_status, i.address
                           FROM volunteer_assignments va
                           JOIN disaster_incidents i ON i.id = va.incident_id
                           WHERE va.volunteer_id = ?
                           ORDER BY va.created_at DESC');
    $stmt->execute([$volunteerId]);
    $assignments = $stmt->fetchAll();
}

$openIncidents = fetchIncidents($pdo, ['open' => true, 'limit' => 8]);

$pageTitle = 'My Tasks';
$pageSubtitle = 'Assignments from the coordination team and their current status.';

include_once __DIR__ . '/../includes/header.php';
?>
<?php if ($notice): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if (!$volunteerId): ?>
    <div class="alert alert-warning">Your volunteer profile has not been set up yet. Ask an administrator to add you on the Volunteers page.</div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h4 class="fw-bold mb-1">Assigned Tasks</h4>
            <p class="text-muted">Status changes are relayed to the incident reporter and responder.</p>
            <?php if (!$assignments): ?>
                <p class="text-muted mb-0">No tasks assigned to you yet.</p>
            <?php endif; ?>
            <?php foreach ($assignments as $assignment): ?>
                <div class="card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                            <div class="text-muted"><small><?php echo htmlspecialchars($assignment['incident_type']); ?> &middot; severity <?php echo htmlspecialchars($assignment['severity']); ?> &middot; <?php echo htmlspecialchars($assignment['address'] ?? 'No location given'); ?></small></div>
                        </div>
                        <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', $assignment['status'])); ?></span>
                    </div>
                    <p class="mt-2 mb-2"><?php echo htmlspecialchars($assignment['assignment_note'] ?? 'No note provided.'); ?></p>
                    <form method="post" class="d-flex flex-wrap gap-2">
                        <input type="hidden" name="assignment_id" value="<?php echo (int) $assignment['id']; ?>">
                        <button class="btn btn-sm btn-outline-primary" type="submit" name="status" value="accepted">Accept</button>
                        <button class="btn btn-sm btn-outline-primary" type="submit" name="status" value="in_progress">Start</button>
                        <button class="btn btn-sm btn-outline-success" type="submit" name="status" value="completed">Complete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h4 class="fw-bold mb-1">Open Incidents Nearby</h4>
            <p class="text-muted">Situational awareness while you are on duty.</p>
            <ul class="list-group">
                <?php if ($openIncidents): foreach ($openIncidents as $incident): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                        <span>
                            <?php echo htmlspecialchars($incident['title']); ?>
                            <div class="text-muted"><small><?php echo htmlspecialchars($incident['incident_type']); ?></small></div>
                        </span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', $incident['status'])); ?></span>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">No open incidents.</li>
                <?php endif; ?>
            </ul>
            <a class="btn btn-outline-primary mt-3" href="<?php echo BASE_URL; ?>/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>Open the map</a>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
