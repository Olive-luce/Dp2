<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['citizen', 'volunteer']);
include_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Announcements</h4>
                <span class="badge bg-info">Live</span>
            </div>
            <ul id="announcementList" class="list-group list-group-flush"></ul>
        </div>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Notifications</h4>
                <span id="notificationBadge" class="badge bg-danger">0</span>
            </div>
            <ul id="notificationList" class="list-group list-group-flush"></ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>/assets/js/communications.js"></script>
