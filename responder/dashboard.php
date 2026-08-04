<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['responder']);
$pageTitle = 'Responder Dashboard';
$pageSubtitle = 'Field operations, assignments, and live incident status.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Live Incidents</h6><h2 class="fw-bold">2</h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Resources Ready</h6><h2 class="fw-bold">2</h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Shelters Open</h6><h2 class="fw-bold">2</h2></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Current Field Operations</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Incident</th><th>Severity</th><th>Assigned Team</th><th>Status</th></tr></thead>
                    <tbody>
                        <tr><td>Flooded Road Near Market</td><td><span class="badge bg-danger">High</span></td><td>Maria Cruz</td><td>In Progress</td></tr>
                        <tr><td>Power Outage in District 3</td><td><span class="badge bg-warning text-dark">Medium</span></td><td>Maria Cruz</td><td>Acknowledged</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
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
