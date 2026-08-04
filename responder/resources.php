<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['responder']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resourceId = (int) ($_POST['resource_id'] ?? 0);
    $incidentId = (int) ($_POST['incident_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);

    $stmt = $pdo->prepare('SELECT r.quantity - COALESCE((SELECT SUM(quantity) FROM resource_allocations WHERE resource_id = r.id), 0) AS remaining, r.name FROM resources r WHERE r.id = ?');
    $stmt->execute([$resourceId]);
    $resource = $stmt->fetch();

    if (!$resource) {
        $error = 'Choose a resource to allocate.';
    } elseif ($quantity < 1) {
        $error = 'Quantity must be at least 1.';
    } elseif ($quantity > (int) $resource['remaining']) {
        $error = 'Only ' . (int) $resource['remaining'] . ' units of ' . $resource['name'] . ' remain.';
    } else {
        $insert = $pdo->prepare('INSERT INTO resource_allocations (resource_id, incident_id, quantity, allocated_to, status) VALUES (?, ?, ?, ?, "allocated")');
        $insert->execute([$resourceId, $incidentId ?: null, $quantity, $_SESSION['full_name'] ?? 'Responder']);

        if ($incidentId) {
            logIncidentUpdate($pdo, $incidentId, $quantity . ' x ' . $resource['name'] . ' allocated.', 'in_progress');
            notifyIncidentParticipants($pdo, $incidentId, $quantity . ' x ' . $resource['name'] . ' dispatched to incident #' . $incidentId . '.');
        }

        $notice = 'Allocated ' . $quantity . ' x ' . $resource['name'] . '.';
    }
}

$resources = $pdo->query('SELECT r.id, r.name, r.category, r.quantity, r.unit, r.location, r.status,
                                 COALESCE(SUM(a.quantity), 0) AS distributed
                          FROM resources r
                          LEFT JOIN resource_allocations a ON a.resource_id = r.id
                          GROUP BY r.id
                          ORDER BY r.name')->fetchAll();

$openIncidents = fetchIncidents($pdo, ['open' => true]);

$myAllocationsStmt = $pdo->prepare('SELECT a.quantity, a.created_at, r.name AS resource_name, r.unit, i.title AS incident_title
                                    FROM resource_allocations a
                                    JOIN resources r ON r.id = a.resource_id
                                    LEFT JOIN disaster_incidents i ON i.id = a.incident_id
                                    WHERE a.allocated_to = ?
                                    ORDER BY a.created_at DESC LIMIT 10');
$myAllocationsStmt->execute([$_SESSION['full_name'] ?? 'Responder']);
$myAllocations = $myAllocationsStmt->fetchAll();

$pageTitle = 'Resource Allocation';
$pageSubtitle = 'Check stock levels and dispatch supplies to active incidents.';

include_once __DIR__ . '/../includes/header.php';
?>
<?php if ($notice): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="table-toolbar">
                <div>
                    <h3 class="card-title">Available Stock</h3>
                    <p class="card-subtitle">Remaining is total quantity minus everything already allocated.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Resource</th><th>Category</th><th>Location</th><th>Total</th><th>Allocated</th><th>Remaining</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$resources): ?>
                            <tr><td class="table-empty" colspan="6">No resources have been registered yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($resources as $resource): ?>
                            <?php $remaining = (int) $resource['quantity'] - (int) $resource['distributed']; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resource['name']); ?></td>
                                <td><?php echo htmlspecialchars($resource['category']); ?></td>
                                <td><?php echo htmlspecialchars($resource['location']); ?></td>
                                <td><?php echo (int) $resource['quantity']; ?> <?php echo htmlspecialchars($resource['unit']); ?></td>
                                <td><?php echo (int) $resource['distributed']; ?></td>
                                <td><span class="badge <?php echo $remaining > 0 ? 'bg-success' : 'bg-danger'; ?>"><?php echo $remaining; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 mb-4">
            <h4 class="fw-bold mb-3">Allocate to an Incident</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" for="resource_id">Resource</label>
                    <select class="form-select" id="resource_id" name="resource_id" required>
                        <option value="">Select a resource</option>
                        <?php foreach ($resources as $resource): ?>
                            <option value="<?php echo (int) $resource['id']; ?>"><?php echo htmlspecialchars($resource['name']); ?> (<?php echo (int) $resource['quantity'] - (int) $resource['distributed']; ?> left)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="incident_id">Incident</label>
                    <select class="form-select" id="incident_id" name="incident_id">
                        <option value="">General stock movement</option>
                        <?php foreach ($openIncidents as $incident): ?>
                            <option value="<?php echo (int) $incident['id']; ?>">#<?php echo (int) $incident['id']; ?> &middot; <?php echo htmlspecialchars($incident['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="quantity">Quantity</label>
                    <input class="form-control" id="quantity" name="quantity" type="number" min="1" value="1" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Dispatch Resource</button>
            </form>
        </div>
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Your Recent Allocations</h4>
            <ul class="list-group">
                <?php if ($myAllocations): foreach ($myAllocations as $allocation): ?>
                    <li class="list-group-item">
                        <strong><?php echo (int) $allocation['quantity']; ?> <?php echo htmlspecialchars($allocation['unit']); ?></strong>
                        of <?php echo htmlspecialchars($allocation['resource_name']); ?>
                        <div class="text-muted"><small><?php echo htmlspecialchars($allocation['incident_title'] ?? 'General stock'); ?> &middot; <?php echo htmlspecialchars($allocation['created_at']); ?></small></div>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">No allocations yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
