<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['admin']);

$pageTitle = 'Admin Command Dashboard';
$pageSubtitle = 'Monitor operations, incidents, volunteers, and response readiness from a unified view.';
$pageActions = '<a href="' . BASE_URL . '/admin/incidents.php" class="btn btn-primary"><i class="fa-solid fa-burst me-2"></i>Manage Incidents</a>';
$baseUrl = BASE_URL;
$pageContent = <<<HTML
<div class="dashboard-grid">
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Total Incidents</p>
                <p class="stat-value">3</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-burst"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Active Volunteers</p>
                <p class="stat-value">1</p>
            </div>
            <div class="stat-icon success"><i class="fa-solid fa-users"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Shelters</p>
                <p class="stat-value">2</p>
            </div>
            <div class="stat-icon warning"><i class="fa-solid fa-person-shelter"></i></div>
        </div>
    </div>
</div>
<div class="dashboard-grid mt-3">
    <div class="card chart-card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Command Center Overview</h3>
                <p class="card-subtitle">Operational readiness across active response zones.</p>
            </div>
        </div>
        <div class="chart-frame"><canvas data-chart></canvas></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Operations Toolkit</h3>
                <p class="card-subtitle">Jump to the most common workflows.</p>
            </div>
        </div>
        <div class="card-body">
            <a class="btn btn-outline-primary w-100 mb-2" href="{$baseUrl}/admin/incidents.php"><i class="fa-solid fa-burst me-2"></i>Manage Incidents</a>
            <a class="btn btn-outline-primary w-100 mb-2" href="{$baseUrl}/admin/volunteers.php"><i class="fa-solid fa-users me-2"></i>Manage Volunteers</a>
            <a class="btn btn-outline-primary w-100 mb-2" href="{$baseUrl}/modules/incidents/map.php"><i class="fa-solid fa-map-location-dot me-2"></i>View Live Map</a>
            <a class="btn btn-outline-primary w-100" href="{$baseUrl}/admin/reports.php"><i class="fa-solid fa-chart-line me-2"></i>View Reports</a>
        </div>
    </div>
</div>
<div class="card mt-3">
    <div class="card-header">
        <div>
            <h3 class="card-title">Live Coordination Map</h3>
            <p class="card-subtitle">A live view of incident zones and command posture.</p>
        </div>
    </div>
    <div class="map-shell"><div id="overviewMap" data-overview-map></div></div>
</div>
HTML;

include_once __DIR__ . '/../includes/layout.php';
