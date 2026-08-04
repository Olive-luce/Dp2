<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? 'citizen';

if ($method === 'GET') {
    if ($role === 'admin') {
        $stmt = $pdo->query('SELECT v.id, v.availability, v.experience_level, v.status, u.full_name, u.username, u.email FROM volunteers v JOIN users u ON u.id = v.user_id ORDER BY v.id DESC');
        $volunteers = $stmt->fetchAll();

        foreach ($volunteers as &$volunteer) {
            $skillsStmt = $pdo->prepare('SELECT skill_name, proficiency FROM volunteer_skills WHERE volunteer_id = ?');
            $skillsStmt->execute([$volunteer['id']]);
            $volunteer['skills'] = $skillsStmt->fetchAll();

            $assignmentsStmt = $pdo->prepare('SELECT va.id, va.assignment_note, va.status, i.title FROM volunteer_assignments va JOIN disaster_incidents i ON i.id = va.incident_id WHERE va.volunteer_id = ? ORDER BY va.created_at DESC');
            $assignmentsStmt->execute([$volunteer['id']]);
            $volunteer['assignments'] = $assignmentsStmt->fetchAll();
        }

        echo json_encode($volunteers);
        exit;
    }

    $stmt = $pdo->prepare('SELECT v.id, v.availability, v.experience_level, v.status FROM volunteers v JOIN users u ON u.id = v.user_id WHERE u.id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $volunteer = $stmt->fetch();

    if (!$volunteer) {
        echo json_encode(['success' => false, 'message' => 'Volunteer profile not found']);
        exit;
    }

    $skillsStmt = $pdo->prepare('SELECT skill_name, proficiency FROM volunteer_skills WHERE volunteer_id = ?');
    $skillsStmt->execute([$volunteer['id']]);
    $volunteer['skills'] = $skillsStmt->fetchAll();

    $assignmentsStmt = $pdo->prepare('SELECT va.id, va.assignment_note, va.status, i.title FROM volunteer_assignments va JOIN disaster_incidents i ON i.id = va.incident_id WHERE va.volunteer_id = ? ORDER BY va.created_at DESC');
    $assignmentsStmt->execute([$volunteer['id']]);
    $volunteer['assignments'] = $assignmentsStmt->fetchAll();

    echo json_encode(['success' => true, 'volunteer' => $volunteer]);
    exit;
}

if ($method === 'POST') {
    if ($role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admins can register volunteers']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $username = trim($payload['username'] ?? '');
    $fullName = trim($payload['full_name'] ?? '');
    $email = trim($payload['email'] ?? '');
    $availability = trim($payload['availability'] ?? 'available');
    $experience = trim($payload['experience_level'] ?? 'beginner');
    $skills = $payload['skills'] ?? [];

    if (!$username || !$fullName || !$email) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $userStmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $userStmt->execute([$username, $email]);
    $user = $userStmt->fetch();

    if (!$user) {
        $passwordHash = password_hash('Volunteer2026!', PASSWORD_DEFAULT);
        $insertUser = $pdo->prepare('INSERT INTO users (username, full_name, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, (SELECT id FROM roles WHERE role_name = "volunteer" LIMIT 1), "active")');
        $insertUser->execute([$username, $fullName, $email, $passwordHash]);
        $userId = (int)$pdo->lastInsertId();
    } else {
        $userId = (int)$user['id'];
    }

    $volunteerStmt = $pdo->prepare('SELECT id FROM volunteers WHERE user_id = ? LIMIT 1');
    $volunteerStmt->execute([$userId]);
    $existingVolunteer = $volunteerStmt->fetch();

    if (!$existingVolunteer) {
        $insertVolunteer = $pdo->prepare('INSERT INTO volunteers (user_id, availability, experience_level, status) VALUES (?, ?, ?, "active")');
        $insertVolunteer->execute([$userId, $availability, $experience]);
        $volunteerId = (int)$pdo->lastInsertId();
    } else {
        $volunteerId = (int)$existingVolunteer['id'];
        $updateVolunteer = $pdo->prepare('UPDATE volunteers SET availability = ?, experience_level = ? WHERE id = ?');
        $updateVolunteer->execute([$availability, $experience, $volunteerId]);
    }

    $pdo->prepare('DELETE FROM volunteer_skills WHERE volunteer_id = ?')->execute([$volunteerId]);
    foreach ($skills as $skill) {
        $skillName = trim($skill['skill_name'] ?? '');
        if ($skillName) {
            $skillStmt = $pdo->prepare('INSERT INTO volunteer_skills (volunteer_id, skill_name, proficiency, status) VALUES (?, ?, ?, "active")');
            $skillStmt->execute([$volunteerId, $skillName, trim($skill['proficiency'] ?? 'intermediate')]);
        }
    }

    echo json_encode(['success' => true, 'volunteer_id' => $volunteerId]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
