<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/incidents.php';

header('Content-Type: application/json');

echo json_encode(fetchIncidents($pdo, ['limit' => 10]));
