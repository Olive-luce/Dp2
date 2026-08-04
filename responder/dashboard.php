<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['responder']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$openIncidents = fetchIncidents($pdo, ['open' => true]);
$myIncidents = fetchIncidents($pdo, ['assigned_to' => $userId, 'open' => true]);

$resourcesReady = (int) $pdo->query("SELECT COUNT(*) FROM resources WHERE status = 'available'")->fetchColumn();
$sheltersOpen = (int) $pdo->query("SELECT COUNT(*) FROM shelters WHERE status <> 'closed'")->fetchColumn();

$pageTitle = 'Responder Dashboard';
$pageSubtitle = 'Field operations, assignments, and live incident status.';
$pageActions = '<a href="' . BASE_URL . '/responder/incidents.php" class="btn btn-primary"><i class="fa-solid fa-burst me-2"></i>Open Dispatch Board</a>';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Live Incidents</h6><h2 class="fw-bold"><?php echo count($openIncidents); ?></h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Resources Ready</h6><h2 class="fw-bold"><?php echo $resourcesReady; ?></h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Shelters Open</h6><h2 class="fw-bold"><?php echo $sheltersOpen; ?></h2></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Current Field Operations</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Incident</th><th>Severity</th><th>Assigned</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (!$openIncidents): ?>
                            <tr><td class="table-empty" colspan="4">No open incidents right now.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($openIncidents as $incident): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($incident['title']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($incident['severity']); ?></span></td>
                                <td><?php echo htmlspecialchars($incident['assigned_responder'] ?? 'Unassigned'); ?></td>
                                <td><?php echo htmlspecialchars(str_replace('_', ' ', $incident['status'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 mb-4">
            <h4 class="fw-bold mb-3">Assigned to You</h4>
            <ul class="list-group">
                <?php if ($myIncidents): foreach ($myIncidents as $incident): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                        <span><?php echo htmlspecialchars($incident['title']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', $incident['status'])); ?></span>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">Nothing assigned to you.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Responder Actions</h4>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>View Live Map</a>
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/responder/resources.php"><i class="fa-solid fa-boxes-stacked me-2"></i>Allocate Resources</a>
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/responder/incidents.php"><i class="fa-solid fa-burst me-2"></i>Update Incident Status</a>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
