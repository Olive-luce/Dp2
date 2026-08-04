<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);
$pageTitle = 'Communication Center';
$pageSubtitle = 'Broadcast public advisories and review notifications.';

include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Communication Center</h4>
            <form id="communicationForm">
                <input type="hidden" name="action" value="broadcast">
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Audience</label>
                    <select class="form-select" name="audience">
                        <option value="all">Citizens & Volunteers</option>
                        <option value="citizens">Citizens Only</option>
                        <option value="volunteers">Volunteers Only</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100" type="submit">Broadcast Message</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Announcements</h5>
                <span class="badge bg-info">Live</span>
            </div>
            <ul id="announcementList" class="list-group list-group-flush"></ul>
        </div>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Notifications</h5>
                <span id="notificationBadge" class="badge bg-danger">0</span>
            </div>
            <ul id="notificationList" class="list-group list-group-flush"></ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/communications.js"></script>
