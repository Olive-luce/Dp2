<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
$pageTitle = 'Shelter Management';
$pageSubtitle = 'Monitor shelter capacity, occupancy, and status.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Register Shelter</h4>
            <form id="shelterForm">
                <div class="mb-3"><label class="form-label">Shelter Name</label><input class="form-control" name="name" required></div>
                <div class="mb-3"><label class="form-label">Location</label><input class="form-control" name="address" required></div>
                <div class="mb-3"><label class="form-label">Capacity</label><input type="number" class="form-control" name="capacity" required></div>
                <div class="mb-3"><label class="form-label">Current Occupancy</label><input type="number" class="form-control" name="current_occupancy" required></div>
                <div class="mb-3"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person"></div>
                <div class="mb-3"><label class="form-label">Shelter Status</label><select class="form-select" name="status"><option value="available">Available</option><option value="full">Full</option><option value="closed">Closed</option></select></div>
                <button class="btn btn-primary w-100" type="submit">Save Shelter</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <h4 class="fw-bold mb-3">Shelter Map</h4>
            <div id="shelterMap" class="map-card"></div>
        </div>
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Shelter Directory</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Name</th><th>Location</th><th>Capacity</th><th>Occupancy</th><th>Status</th></tr></thead>
                    <tbody id="shelterList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/shelter-management.js"></script>
