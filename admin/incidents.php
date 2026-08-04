<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
$pageTitle = 'Incident Management';
$pageSubtitle = 'Create, assign, and resolve disaster incidents.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Create Incident</h4>
            <form id="incidentForm">
                <div class="mb-3"><label class="form-label">Title</label><input class="form-control" id="incidentTitle" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="incidentDescription" rows="3" required></textarea></div>
                <div class="mb-3"><label class="form-label">Incident Type</label><input class="form-control" id="incidentType" value="Flood" required></div>
                <div class="mb-3"><label class="form-label">Severity</label><select class="form-select" id="severity"><option>Low</option><option selected>Medium</option><option>High</option><option>Critical</option></select></div>
                <div class="mb-3"><label class="form-label">Priority</label><select class="form-select" id="priority"><option>Low</option><option selected>Medium</option><option>High</option><option>Critical</option></select></div>
                <div class="mb-3"><label class="form-label">Status</label><select class="form-select" id="status"><option>reported</option><option>acknowledged</option><option>in_progress</option><option>resolved</option></select></div>
                <div class="mb-3"><label class="form-label">GPS Latitude</label><input class="form-control" id="latitude"></div>
                <div class="mb-3"><label class="form-label">GPS Longitude</label><input class="form-control" id="longitude"></div>
                <div class="mb-3"><label class="form-label">Assigned Responder</label><input class="form-control" id="assignedTo"></div>
                <div class="mb-3"><label class="form-label">Reporter</label><input class="form-control" id="reporter" value="admin" required></div>
                <div class="mb-3"><label class="form-label">Photo Upload</label><input type="file" class="form-control" id="photo"></div>
                <button class="btn btn-primary w-100" type="submit">Save Incident</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="table-toolbar">
                <div>
                    <h3 class="card-title">Incident Records</h3>
                    <p class="card-subtitle">Live feed of reported incidents across all zones.</p>
                </div>
                <input type="search" id="incidentSearch" class="form-control table-search" placeholder="Search incidents" aria-label="Search incidents">
            </div>
            <div class="table-responsive">
                <table id="incidentTable" class="table">
                    <thead>
                        <tr><th>Title</th><th>Type</th><th>Severity</th><th>Status</th><th>Reporter</th><th>Responder</th><th>Actions</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/incident-management.js"></script>
