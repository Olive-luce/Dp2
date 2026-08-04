<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$role = $_SESSION['role'] ?? 'citizen';
if ($role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Only admins can access reports']);
    exit;
}

$daily = $pdo->query("SELECT DATE(created_at) AS label, COUNT(*) AS total FROM disaster_incidents GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 7")->fetchAll();
$weekly = $pdo->query("SELECT WEEK(created_at, 1) AS label, COUNT(*) AS total FROM disaster_incidents GROUP BY WEEK(created_at, 1) ORDER BY WEEK(created_at, 1) DESC LIMIT 6")->fetchAll();
$monthly = $pdo->query("SELECT DATE_FORMAT(created_at, '%M %Y') AS label, COUNT(*) AS total FROM disaster_incidents GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY created_at DESC LIMIT 6")->fetchAll();
$population = $pdo->query("SELECT SUM(current_occupancy) AS total FROM shelters")->fetchColumn();
$volunteerActivities = $pdo->query("SELECT COUNT(*) AS total FROM volunteer_assignments")->fetchColumn();
$shelterOccupancy = $pdo->query("SELECT SUM(current_occupancy) AS total, SUM(capacity) AS capacity FROM shelters")->fetch(PDO::FETCH_ASSOC);
$resourceConsumption = $pdo->query("SELECT SUM(quantity) AS total FROM resource_allocations")->fetchColumn();
$responseTime = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) AS avg_minutes FROM disaster_incidents WHERE status = 'resolved'")->fetchColumn();

echo json_encode([
    'dailyIncidents' => array_reverse($daily),
    'weeklyIncidents' => array_reverse($weekly),
    'monthlyIncidents' => array_reverse($monthly),
    'affectedPopulation' => (int)($population ?: 0),
    'volunteerActivities' => (int)($volunteerActivities ?: 0),
    'shelterOccupancy' => [
        'occupied' => (int)($shelterOccupancy['total'] ?: 0),
        'capacity' => (int)($shelterOccupancy['capacity'] ?: 0)
    ],
    'resourceConsumption' => (int)($resourceConsumption ?: 0),
    'responseTime' => round((float)($responseTime ?: 0), 2)
]);
