<?php
require_once __DIR__ . '/config/dbconnection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['role'] === 'responder' ? 'responder/dashboard.php' : ($_SESSION['role'] === 'volunteer' ? 'volunteer/dashboard.php' : 'citizen/dashboard.php'))));
    exit;
}

$pageTitle = 'Community Disaster Response Platform';
$pageSubtitle = 'Modern coordination for incidents, volunteers, shelters, resources, and public communications.';
$baseUrl = BASE_URL;
$pageContent = <<<HTML
<div class="dashboard-grid">
    <div class="card hero-panel animate-on-scroll">
        <span class="badge badge-primary mb-2">Community Safety Network</span>
        <h2 class="mb-2">Coordinate disaster response with clarity and speed.</h2>
        <p class="text-secondary mb-3">A professional emergency operations platform for incident management, volunteer coordination, shelter readiness, resource tracking, and real-time communications.</p>
        <div class="hero-actions">
            <a href="{$baseUrl}/auth/register.php" class="btn btn-primary">Create Account</a>
            <a href="{$baseUrl}/auth/login.php" class="btn btn-outline-primary">Login</a>
        </div>
    </div>
    <div class="card animate-on-scroll">
        <div class="card-header">
            <div>
                <h3 class="card-title">Platform Highlights</h3>
                <p class="card-subtitle">Built for modern emergency operations.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="info-stack">
                <div class="info-item"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Interactive incident map</div>
                <div class="info-item"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Role-based dashboards</div>
                <div class="info-item"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Resource and shelter tracking</div>
                <div class="info-item"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Operational reports</div>
            </div>
        </div>
    </div>
</div>
<div class="dashboard-grid mt-3">
    <div class="card stat-card animate-on-scroll">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Active Incidents</p>
                <p class="stat-value">24</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-burst"></i></div>
        </div>
    </div>
    <div class="card stat-card animate-on-scroll">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Registered Volunteers</p>
                <p class="stat-value">18</p>
            </div>
            <div class="stat-icon success"><i class="fa-solid fa-users"></i></div>
        </div>
    </div>
    <div class="card stat-card animate-on-scroll">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="stat-label">Available Shelters</p>
                <p class="stat-value">4</p>
            </div>
            <div class="stat-icon warning"><i class="fa-solid fa-person-shelter"></i></div>
        </div>
    </div>
</div>
HTML;

include_once __DIR__ . '/includes/layout.php';
