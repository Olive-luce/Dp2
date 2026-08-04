<?php
require_once __DIR__ . '/../config/dbconnection.php';
header('Content-Type: application/json');

if (!$pdo) {
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

$stmt = $pdo->query('SELECT id, title, severity, status FROM incidents ORDER BY created_at DESC LIMIT 10');
echo json_encode($stmt->fetchAll());
