<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/incidents.php';

requireAuth(['citizen']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$myReports = fetchIncidents($pdo, ['reported_by' => $userId]);
$reportedCount = count($myReports);

$shelter = $pdo->query('SELECT SUM(current_occupancy) AS occupied, SUM(capacity) AS capacity FROM shelters')->fetch();
$shelterCapacity = (int) ($shelter['capacity'] ?? 0) > 0
    ? round(((int) $shelter['occupied'] / (int) $shelter['capacity']) * 100) . '%'
    : 'n/a';

$announcements = $pdo->query('SELECT title, body FROM announcements ORDER BY created_at DESC LIMIT 5')->fetchAll();
$updateCount = count($announcements);

$updatesHtml = '';
foreach ($announcements as $announcement) {
    $updatesHtml .= '<div class="info-item"><strong>' . htmlspecialchars($announcement['title']) . '</strong><br>' . htmlspecialchars($announcement['body']) . '</div>';
}
foreach (array_slice($myReports, 0, 3) as $report) {
    $updatesHtml .= '<div class="info-item">' . htmlspecialchars($report['title']) . ' &middot; <strong>' . htmlspecialchars(str_replace('_', ' ', $report['status'])) . '</strong></div>';
}
if ($updatesHtml === '') {
    $updatesHtml = '<div class="info-item text-muted">No public updates yet.</div>';
}

$pageTitle = 'Citizen Dashboard';
$pageSubtitle = 'Track public safety updates and access the services you need.';
$pageActions = '<a href="' . BASE_URL . '/citizen/report.php" class="btn btn-primary"><i class="fa-solid fa-exclamation me-2"></i>Report Incident</a>';
$baseUrl = BASE_URL;
$pageContent = <<<HTML
<div class="dashboard-grid">
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Reported Issues</p>
                <p class="stat-value">{$reportedCount}</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-burst"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Shelter Occupancy</p>
                <p class="stat-value">{$shelterCapacity}</p>
            </div>
            <div class="stat-icon success"><i class="fa-solid fa-person-shelter"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Public Updates</p>
                <p class="stat-value">{$updateCount}</p>
            </div>
            <div class="stat-icon warning"><i class="fa-solid fa-bullhorn"></i></div>
        </div>
    </div>
</div>
<div class="dashboard-grid mt-3">
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Emergency Updates</h3>
                <p class="card-subtitle">Latest operational announcements.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="info-stack">{$updatesHtml}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Citizen Actions</h3>
                <p class="card-subtitle">Quick access to public services.</p>
            </div>
        </div>
        <div class="card-body">
            <a class="btn btn-outline-primary w-100 mb-2" href="{$baseUrl}/citizen/report.php"><i class="fa-solid fa-exclamation me-2"></i>Report Incident</a>
            <a class="btn btn-outline-primary w-100 mb-2" href="{$baseUrl}/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>View Disaster Map</a>
            <a class="btn btn-outline-primary w-100" href="{$baseUrl}/citizen/shelters.php"><i class="fa-solid fa-person-shelter me-2"></i>View Nearby Shelters</a>
        </div>
    </div>
</div>
HTML;

include_once __DIR__ . '/../includes/layout.php';
