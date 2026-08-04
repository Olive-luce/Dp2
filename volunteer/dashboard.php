<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['volunteer']);
$pageTitle = 'Volunteer Dashboard';
$pageSubtitle = 'Your assignments, skills, and availability at a glance.';

include_once __DIR__ . '/../includes/header.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$stmt = $pdo->prepare('SELECT v.id, v.availability, v.experience_level, v.status FROM volunteers v JOIN users u ON u.id = v.user_id WHERE u.id = ? LIMIT 1');
$stmt->execute([$userId]);
$volunteer = $stmt->fetch();
$volunteerId = $volunteer['id'] ?? 0;

$skillsStmt = $pdo->prepare('SELECT skill_name, proficiency FROM volunteer_skills WHERE volunteer_id = ?');
$skillsStmt->execute([$volunteerId]);
$skills = $skillsStmt->fetchAll();

$assignmentsStmt = $pdo->prepare('SELECT va.status, va.assignment_note, i.title FROM volunteer_assignments va JOIN disaster_incidents i ON i.id = va.incident_id WHERE va.volunteer_id = ? ORDER BY va.created_at DESC');
$assignmentsStmt->execute([$volunteerId]);
$assignments = $assignmentsStmt->fetchAll();
?>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Assigned Incidents</h6><h2 class="fw-bold"><?php echo count($assignments); ?></h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Availability</h6><h2 class="fw-bold"><?php echo htmlspecialchars($volunteer['availability'] ?? 'available'); ?></h2></div></div>
    <div class="col-md-4"><div class="card p-4 stat-card"><h6 class="text-muted">Statistics</h6><h2 class="fw-bold"><?php echo count($skills); ?> Skills</h2></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Assigned Incidents</h4>
            <ul class="list-group">
                <?php if ($assignments): foreach ($assignments as $assignment): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($assignment['title']); ?><br><small class="text-muted"><?php echo htmlspecialchars($assignment['assignment_note'] ?? 'No note provided'); ?></small></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($assignment['status']); ?></span>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">No assignments yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Volunteer Profile</h4>
            <p class="mb-2"><strong>Experience:</strong> <?php echo htmlspecialchars($volunteer['experience_level'] ?? 'beginner'); ?></p>
            <p class="mb-2"><strong>Status:</strong> <?php echo htmlspecialchars($volunteer['status'] ?? 'active'); ?></p>
            <p class="mb-3"><strong>Skills:</strong> <?php echo htmlspecialchars(implode(', ', array_map(function ($s) { return $s['skill_name']; }, $skills))); ?></p>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/volunteer/tasks.php"><i class="fa-solid fa-list-check me-2"></i>View My Tasks</a>
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/volunteer/checkin.php"><i class="fa-solid fa-location-crosshairs me-2"></i>Check In</a>
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>View Incident Locations</a>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
