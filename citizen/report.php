<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['citizen']);
$pageTitle = 'Report an Incident';
$pageSubtitle = 'Tell responders what is happening near you.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4">
    <h3 class="fw-bold mb-3">Report an Incident</h3>
    <form>
        <div class="mb-3">
            <label class="form-label">Incident Type</label>
            <input class="form-control" placeholder="Flood / Fire / Medical">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="4" placeholder="Describe what is happening"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input class="form-control" placeholder="Street / Landmark">
        </div>
        <button class="btn btn-primary">Submit Report</button>
    </form>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
