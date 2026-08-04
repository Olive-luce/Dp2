<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['responder']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentId = (int) ($_POST['incident_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($incidentId && $action === 'claim') {
        $stmt = $pdo->prepare('UPDATE disaster_incidents SET assigned_to = ? WHERE id = ?');
        $stmt->execute([$userId, $incidentId]);
        setIncidentStatus($pdo, $incidentId, 'acknowledged', ($_SESSION['full_name'] ?? 'A responder') . ' claimed this incident.');
        $notice = 'Incident #' . $incidentId . ' assigned to you.';
    } elseif ($incidentId && $action === 'status') {
        $status = $_POST['status'] ?? 'acknowledged';
        setIncidentStatus($pdo, $incidentId, $status, trim($_POST['note'] ?? ''));
        $notice = 'Incident #' . $incidentId . ' updated.';
    }
}

$myIncidents = fetchIncidents($pdo, ['assigned_to' => $userId, 'open' => true]);
$unassigned = array_values(array_filter(fetchIncidents($pdo, ['open' => true]), function ($incident) {
    return empty($incident['assigned_to']);
}));

$pageTitle = 'Dispatch Board';
$pageSubtitle = 'Claim incoming incidents and relay status updates to reporters.';

include_once __DIR__ . '/../includes/header.php';
?>
<?php if ($notice): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card p-4">
            <h4 class="fw-bold mb-1">Your Active Incidents</h4>
            <p class="text-muted">Updates you post here are sent to the reporter.</p>
            <?php if (!$myIncidents): ?>
                <p class="text-muted mb-0">Nothing assigned to you right now.</p>
            <?php endif; ?>
            <?php foreach ($myIncidents as $incident): ?>
                <div class="card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <strong><?php echo htmlspecialchars($incident['title']); ?></strong>
                            <div class="text-muted"><small><?php echo htmlspecialchars($incident['incident_type']); ?> &middot; severity <?php echo htmlspecialchars($incident['severity']); ?> &middot; reported by <?php echo htmlspecialchars($incident['reporter'] ?? 'Unknown'); ?></small></div>
                        </div>
                        <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', $incident['status'])); ?></span>
                    </div>
                    <p class="mt-2 mb-2"><?php echo htmlspecialchars($incident['description']); ?></p>
                    <form method="post" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="hidden" name="incident_id" value="<?php echo (int) $incident['id']; ?>">
                        <input type="hidden" name="action" value="status">
                        <select class="form-select" name="status" style="max-width: 180px;">
                            <?php foreach (INCIDENT_STATUSES as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $incident['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $status)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input class="form-control" name="note" style="max-width: 320px;" placeholder="Update for the reporter">
                        <button class="btn btn-primary" type="submit">Post Update</button>
                    </form>
                    <?php $updates = fetchIncidentUpdates($pdo, (int) $incident['id']); ?>
                    <?php if ($updates): ?>
                        <ul class="list-group mt-3">
                            <?php foreach (array_slice($updates, 0, 4) as $update): ?>
                                <li class="list-group-item">
                                    <small class="text-muted"><?php echo htmlspecialchars($update['created_at']); ?></small><br>
                                    <?php echo htmlspecialchars($update['update_text']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4">
            <h4 class="fw-bold mb-1">Unassigned Queue</h4>
            <p class="text-muted">New reports waiting for a responder.</p>
            <ul class="list-group">
                <?php if ($unassigned): foreach ($unassigned as $incident): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong><?php echo htmlspecialchars($incident['title']); ?></strong>
                                <div class="text-muted"><small><?php echo htmlspecialchars($incident['incident_type']); ?> &middot; <?php echo htmlspecialchars($incident['address'] ?? 'No location given'); ?></small></div>
                            </div>
                            <form method="post">
                                <input type="hidden" name="incident_id" value="<?php echo (int) $incident['id']; ?>">
                                <input type="hidden" name="action" value="claim">
                                <button class="btn btn-sm btn-outline-primary" type="submit">Claim</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">The queue is clear.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
