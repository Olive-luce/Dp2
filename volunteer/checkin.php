<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['volunteer']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$notice = '';

$volunteerStmt = $pdo->prepare('SELECT id, availability, experience_level, status FROM volunteers WHERE user_id = ? LIMIT 1');
$volunteerStmt->execute([$userId]);
$volunteer = $volunteerStmt->fetch();
$volunteerId = (int) ($volunteer['id'] ?? 0);

$availabilityOptions = ['available', 'on_duty', 'unavailable'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $volunteerId) {
    $availability = $_POST['availability'] ?? '';

    if (in_array($availability, $availabilityOptions, true)) {
        $stmt = $pdo->prepare('UPDATE volunteers SET availability = ? WHERE id = ?');
        $stmt->execute([$availability, $volunteerId]);
        $volunteer['availability'] = $availability;

        $note = trim($_POST['note'] ?? '');
        $message = ($_SESSION['full_name'] ?? 'A volunteer') . ' checked in as ' . str_replace('_', ' ', $availability) . ($note ? ' — ' . $note : '') . '.';

        $admins = $pdo->query("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE r.name IN ('admin', 'responder')")->fetchAll();
        $insert = $pdo->prepare('INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, "checkin", "unread")');
        foreach ($admins as $admin) {
            $insert->execute([(int) $admin['id'], $message]);
        }

        $notice = 'Checked in as ' . str_replace('_', ' ', $availability) . '. The coordination team has been notified.';
    }
}

$skillsStmt = $pdo->prepare('SELECT skill_name, proficiency FROM volunteer_skills WHERE volunteer_id = ?');
$skillsStmt->execute([$volunteerId]);
$skills = $skillsStmt->fetchAll();

$historyStmt = $pdo->prepare('SELECT DISTINCT message, created_at FROM notifications WHERE notification_type = "checkin" AND message LIKE ? ORDER BY created_at DESC LIMIT 5');
$historyStmt->execute([($_SESSION['full_name'] ?? '') . '%']);
$history = $historyStmt->fetchAll();

$pageTitle = 'Volunteer Check-in';
$pageSubtitle = 'Set your availability so coordinators know who is on duty.';

include_once __DIR__ . '/../includes/header.php';
?>
<?php if ($notice): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if (!$volunteerId): ?>
    <div class="alert alert-warning">Your volunteer profile has not been set up yet. Ask an administrator to add you on the Volunteers page.</div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h4 class="fw-bold mb-1">Current Status</h4>
            <p class="text-muted">You are currently <strong><?php echo htmlspecialchars(str_replace('_', ' ', $volunteer['availability'] ?? 'unavailable')); ?></strong>.</p>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" for="availability">Availability</label>
                    <select class="form-select" id="availability" name="availability" required>
                        <?php foreach ($availabilityOptions as $option): ?>
                            <option value="<?php echo $option; ?>" <?php echo ($volunteer['availability'] ?? '') === $option ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $option)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="note">Note for coordinators</label>
                    <input class="form-control" id="note" name="note" placeholder="At the Mirpur shelter until 6pm">
                </div>
                <button class="btn btn-primary w-100" type="submit" <?php echo $volunteerId ? '' : 'disabled'; ?>>Check In</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 mb-4">
            <h4 class="fw-bold mb-3">Your Profile</h4>
            <p class="mb-2"><strong>Experience:</strong> <?php echo htmlspecialchars($volunteer['experience_level'] ?? 'beginner'); ?></p>
            <p class="mb-2"><strong>Status:</strong> <?php echo htmlspecialchars($volunteer['status'] ?? 'active'); ?></p>
            <p class="mb-0"><strong>Skills:</strong> <?php echo htmlspecialchars($skills ? implode(', ', array_column($skills, 'skill_name')) : 'None recorded'); ?></p>
        </div>
        <div class="card p-4">
            <h4 class="fw-bold mb-3">Recent Check-ins</h4>
            <ul class="list-group">
                <?php if ($history): foreach ($history as $entry): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($entry['message']); ?>
                        <div class="text-muted"><small><?php echo htmlspecialchars($entry['created_at']); ?></small></div>
                    </li>
                <?php endforeach; else: ?>
                    <li class="list-group-item text-muted">No check-ins recorded yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
