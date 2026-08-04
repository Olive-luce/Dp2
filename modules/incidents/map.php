<?php
require_once __DIR__ . '/../../config/dbconnection.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAuth(['admin', 'responder', 'volunteer', 'citizen']);

$pageTitle = 'Disaster Interactive Map';
$pageSubtitle = 'Map view with incident pins, search, and role-based reporting.';
$pageActions = '<button class="btn btn-outline-primary" id="toggleSatellite"><i class="fa-solid fa-satellite-dish me-2"></i>Toggle Satellite</button>'
    . '<button class="btn btn-primary" id="locateMe"><i class="fa-solid fa-location-crosshairs me-2"></i>Locate Me</button>';

include_once __DIR__ . '/../../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-3">
            <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                <input type="text" id="mapSearch" class="form-control" style="max-width: 280px;" placeholder="Search Bangladesh location">
                <button class="btn btn-outline-secondary" id="searchLocation"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
            </div>
            <div class="map-card">
                <div id="map"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Add Incident Report</h4>
            <form id="incidentForm">
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <div class="mb-3">
                    <label class="form-label">Incident Type</label>
                    <select class="form-select" id="incidentType" name="incident_type" required>
                        <option value="Flood">Flood</option>
                        <option value="Fire">Fire</option>
                        <option value="Earthquake">Earthquake</option>
                        <option value="Cyclone">Cyclone</option>
                        <option value="Road Block">Road Block</option>
                        <option value="Medical Emergency">Medical Emergency</option>
                        <option value="Infrastructure Damage">Infrastructure Damage</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Severity</label>
                    <select class="form-select" id="severity" name="severity" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reporter</label>
                    <input type="text" class="form-control" id="reporter" name="reporter" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? 'System'); ?>" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Save Report</button>
            </form>
            <div id="mapFeedback" class="alert alert-info mt-3 d-none"></div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
