<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? 'citizen';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'notifications';

    if ($action === 'unread_count') {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS unread_count FROM notifications WHERE recipient_id = ? AND status = "unread"');
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'unread_count' => (int) $stmt->fetchColumn()]);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, message, notification_type, status, created_at FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $announcementStmt = $pdo->prepare('SELECT id, title, body, created_at FROM announcements WHERE status = "published" ORDER BY created_at DESC LIMIT 10');
    $announcementStmt->execute();
    $announcements = $announcementStmt->fetchAll();

    echo json_encode(['success' => true, 'notifications' => $notifications, 'announcements' => $announcements]);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $payload['action'] ?? $_POST['action'] ?? '';

    if ($action === 'broadcast') {
        $message = trim($payload['message'] ?? '');
        $type = trim($payload['type'] ?? 'info');
        $audience = trim($payload['audience'] ?? 'all');

        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Message is required']);
            exit;
        }

        $where = [];
        if ($audience === 'citizens') {
            $where[] = "role = 'citizen'";
        } elseif ($audience === 'volunteers') {
            $where[] = "role = 'volunteer'";
        } else {
            $where[] = "role IN ('citizen', 'volunteer')";
        }

        $recipientSql = 'SELECT id, email FROM users WHERE ' . implode(' AND ', $where);
        $recipientStmt = $pdo->query($recipientSql);
        $recipients = $recipientStmt->fetchAll();

        foreach ($recipients as $recipient) {
            $insert = $pdo->prepare('INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, ?, "unread")');
            $insert->execute([$recipient['id'], $message, $type]);
        }

        echo json_encode(['success' => true, 'sent' => count($recipients)]);
        exit;
    }

    if ($action === 'announcement') {
        $title = trim($payload['title'] ?? '');
        $body = trim($payload['body'] ?? '');
        $audience = trim($payload['audience'] ?? 'all');

        if (!$title || !$body) {
            echo json_encode(['success' => false, 'message' => 'Title and body are required']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO announcements (title, body, published_by, status) VALUES (?, ?, ?, "published")');
        $stmt->execute([$title, $body, $userId]);

        $announcementId = $pdo->lastInsertId();
        $where = [];
        if ($audience === 'citizens') {
            $where[] = "role = 'citizen'";
        } elseif ($audience === 'volunteers') {
            $where[] = "role = 'volunteer'";
        } else {
            $where[] = "role IN ('citizen', 'volunteer')";
        }

        $recipientStmt = $pdo->query('SELECT id, email FROM users WHERE ' . implode(' AND ', $where));
        $recipients = $recipientStmt->fetchAll();
        foreach ($recipients as $recipient) {
            $notify = $pdo->prepare('INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, "announcement", "unread")');
            $notify->execute([$recipient['id'], 'New announcement: ' . $title]);
        }

        echo json_encode(['success' => true, 'announcement_id' => $announcementId]);
        exit;
    }

    if ($action === 'email_alert') {
        $message = trim($payload['message'] ?? '');
        $title = trim($payload['title'] ?? 'Emergency Alert');
        $audience = trim($payload['audience'] ?? 'all');

        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Message is required']);
            exit;
        }

        $where = [];
        if ($audience === 'citizens') {
            $where[] = "role = 'citizen'";
        } elseif ($audience === 'volunteers') {
            $where[] = "role = 'volunteer'";
        } else {
            $where[] = "role IN ('citizen', 'volunteer')";
        }

        $recipientStmt = $pdo->query('SELECT id, email FROM users WHERE ' . implode(' AND ', $where));
        $recipients = $recipientStmt->fetchAll();

        foreach ($recipients as $recipient) {
            $notify = $pdo->prepare('INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, "email_alert", "unread")');
            $notify->execute([$recipient['id'], $title . ': ' . $message]);
            if (!empty($recipient['email'])) {
                @mail($recipient['email'], $title, $message, "From: no-reply@localhost\r\n");
            }
        }

        echo json_encode(['success' => true, 'sent' => count($recipients)]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Unsupported request']);
