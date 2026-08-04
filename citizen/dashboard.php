<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth(['citizen']);

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
                <p class="stat-value">2</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-burst"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Shelter Capacity</p>
                <p class="stat-value">86%</p>
            </div>
            <div class="stat-icon success"><i class="fa-solid fa-person-shelter"></i></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Public Updates</p>
                <p class="stat-value">3</p>
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
            <div class="info-stack">
                <div class="info-item">Flooded road near the market is now under active response.</div>
                <div class="info-item">The school gym shelter remains open with available space.</div>
            </div>
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
