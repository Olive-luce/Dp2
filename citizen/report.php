<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['citizen']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$errors = [];
$success = '';
$submitted = [
    'title' => '',
    'incident_type' => '',
    'severity' => 'medium',
    'description' => '',
    'address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = [
        'title' => trim($_POST['title'] ?? ''),
        'incident_type' => trim($_POST['incident_type'] ?? ''),
        'severity' => trim($_POST['severity'] ?? 'medium'),
        'description' => trim($_POST['description'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
    ];

    if ($submitted['title'] === '') {
        $errors[] = 'A short title is required.';
    }
    if ($submitted['incident_type'] === '') {
        $errors[] = 'Please choose an incident type.';
    }
    if ($submitted['description'] === '') {
        $errors[] = 'Please describe what is happening.';
    }

    if (!$errors) {
        $incidentId = createIncident($pdo, [
            'title' => $submitted['title'],
            'description' => $submitted['description'],
            'incident_type' => $submitted['incident_type'],
            'severity' => $submitted['severity'],
            'priority' => $submitted['severity'],
            'status' => 'reported',
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'address' => $submitted['address'] ?: null,
        ], $userId);

        $success = 'Report #' . $incidentId . ' submitted. Responders can see it now.';
        $submitted = ['title' => '', 'incident_type' => '', 'severity' => 'medium', 'description' => '', 'address' => ''];
    }
}

$myReports = fetchIncidents($pdo, ['reported_by' => $userId, 'limit' => 10]);

$pageTitle = 'Report an Incident';
$pageSubtitle = 'Tell responders what is happening near you.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h3 class="fw-bold mb-3">Report an Incident</h3>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($submitted['title']); ?>" placeholder="Flooded road near the market" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="incident_type">Incident Type</label>
                    <select class="form-select" id="incident_type" name="incident_type" required>
                        <option value="">Select a type</option>
                        <?php foreach (['Flood', 'Fire', 'Earthquake', 'Cyclone', 'Road Block', 'Medical Emergency', 'Infrastructure Damage'] as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $submitted['incident_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="severity">Severity</label>
                    <select class="form-select" id="severity" name="severity">
                        <?php foreach (INCIDENT_SEVERITIES as $level): ?>
                            <option value="<?php echo $level; ?>" <?php echo $submitted['severity'] === $level ? 'selected' : ''; ?>><?php echo ucfirst($level); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe what is happening" required><?php echo htmlspecialchars($submitted['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Location</label>
                    <input class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($submitted['address']); ?>" placeholder="Street / landmark">
                </div>
                <button class="btn btn-primary" type="submit">Submit Report</button>
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>Pin it on the map instead</a>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h3 class="fw-bold mb-1">Your Reports</h3>
            <p class="text-muted">Status updates from responders appear here.</p>
            <ul class="list-group">
                <?php if ($myReports): foreach ($myReports as $report): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong><?php echo htmlspecialchars($report['title']); ?></strong>
                                <div class="text-muted"><small><?php echo htmlspecialchars($report['incident_type']); ?> &middot; <?php echo htmlspecialchars($report['created_at']); ?></small></div>
                            </div>
                            <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', $report['status'])); ?></span>
                        </div>
                        <?php $updates = fetchIncidentUpdates($pdo, (int) $report['id']); ?>
                        <?php if ($updates): ?>
                            <div class="mt-2 text-muted">
                                <?php foreach (array_slice($updates, 0, 4) as $update): ?>
                                    <div><small><i class="fa-solid fa-clock-rotate-left me-1"></i><?php echo htmlspecialchars($update['update_text']); ?> &middot; <?php echo htmlspecialchars($update['created_at']); ?></small></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">You have not reported anything yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
