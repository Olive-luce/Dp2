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
            <div id="communicationFeedback" class="alert d-none" role="status"></div>
            <form id="communicationForm">
                <div class="mb-3">
                    <label class="form-label" for="commAction">Send as</label>
                    <select class="form-select" id="commAction" name="action">
                        <option value="broadcast">Direct notification</option>
                        <option value="announcement">Public announcement</option>
                    </select>
                </div>
                <div class="mb-3 d-none" id="commTitleGroup">
                    <label class="form-label" for="commTitle">Title</label>
                    <input class="form-control" id="commTitle" name="title" placeholder="Cyclone shelter opening">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="commMessage">Message</label>
                    <textarea class="form-control" id="commMessage" name="message" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="commType">Type</label>
                    <select class="form-select" id="commType" name="type">
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="commAudience">Audience</label>
                    <select class="form-select" id="commAudience" name="audience">
                        <option value="all">Citizens &amp; Volunteers</option>
                        <option value="citizens">Citizens Only</option>
                        <option value="volunteers">Volunteers Only</option>
                        <option value="responders">Responders Only</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100" type="submit">Send</button>
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
                <div class="d-flex align-items-center gap-2">
                    <span id="notificationBadge" class="badge bg-danger">0</span>
                    <button class="btn btn-sm btn-outline-primary" type="button" id="markAllRead">Mark all read</button>
                </div>
            </div>
            <ul id="notificationList" class="list-group list-group-flush"></ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/communications.js"></script>
