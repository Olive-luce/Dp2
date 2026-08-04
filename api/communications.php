<?php
require_once __DIR__ . '/../config/dbconnection.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

requireApiAuth();

$userId = (int) ($_SESSION['user_id'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Roles live in user_roles/roles, not on the users row.
 *
 * @return array<int, array{id: int, email: string}>
 */
function audienceRecipients(PDO $pdo, string $audience): array
{
    $roles = $audience === 'citizens' ? ['citizen']
        : ($audience === 'volunteers' ? ['volunteer']
        : ($audience === 'responders' ? ['responder'] : ['citizen', 'volunteer']));

    $placeholders = implode(',', array_fill(0, count($roles), '?'));
    $stmt = $pdo->prepare(
        'SELECT DISTINCT u.id, u.email
         FROM users u
         JOIN user_roles ur ON ur.user_id = u.id
         JOIN roles r ON r.id = ur.role_id
         WHERE r.name IN (' . $placeholders . ')'
    );
    $stmt->execute($roles);

    return $stmt->fetchAll();
}

function notifyRecipients(PDO $pdo, array $recipients, string $message, string $type): void
{
    $insert = $pdo->prepare(
        'INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES (?, ?, ?, "unread")'
    );

    foreach ($recipients as $recipient) {
        $insert->execute([$recipient['id'], $message, $type]);
    }
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'notifications';

    $unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND status = "unread"');
    $unreadStmt->execute([$userId]);
    $unreadCount = (int) $unreadStmt->fetchColumn();

    if ($action === 'unread_count') {
        echo json_encode(['success' => true, 'unread_count' => $unreadCount]);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id, message, notification_type, status, created_at
         FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC, id DESC LIMIT 20'
    );
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $announcements = $pdo->query(
        'SELECT a.id, a.title, a.body, a.created_at, u.full_name AS published_by
         FROM announcements a
         LEFT JOIN users u ON u.id = a.published_by
         WHERE a.status = "published"
         ORDER BY a.created_at DESC, a.id DESC LIMIT 10'
    )->fetchAll();

    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'notifications' => $notifications,
        'announcements' => $announcements,
    ]);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $payload['action'] ?? '';

    // Recipients may mark their own notifications read; everything else is admin-only.
    if ($action === 'mark_read') {
        $notificationId = (int) ($payload['id'] ?? 0);

        if ($notificationId) {
            $stmt = $pdo->prepare('UPDATE notifications SET status = "read" WHERE id = ? AND recipient_id = ?');
            $stmt->execute([$notificationId, $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE notifications SET status = "read" WHERE recipient_id = ?');
            $stmt->execute([$userId]);
        }

        echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
        exit;
    }

    requireApiRole(['admin']);

    $audience = trim($payload['audience'] ?? 'all');

    if ($action === 'broadcast') {
        $message = trim($payload['message'] ?? '');
        $type = trim($payload['type'] ?? 'info');

        if ($message === '') {
            echo json_encode(['success' => false, 'message' => 'Message is required']);
            exit;
        }

        $recipients = audienceRecipients($pdo, $audience);
        notifyRecipients($pdo, $recipients, $message, $type);

        echo json_encode([
            'success' => true,
            'sent' => count($recipients),
            'message' => 'Broadcast delivered to ' . count($recipients) . ' recipient(s).',
        ]);
        exit;
    }

    if ($action === 'announcement') {
        $title = trim($payload['title'] ?? '');
        $body = trim($payload['message'] ?? $payload['body'] ?? '');

        if ($title === '' || $body === '') {
            echo json_encode(['success' => false, 'message' => 'Title and message are required']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO announcements (title, body, published_by, status) VALUES (?, ?, ?, "published")');
        $stmt->execute([$title, $body, $userId]);
        $announcementId = (int) $pdo->lastInsertId();

        $recipients = audienceRecipients($pdo, $audience);
        notifyRecipients($pdo, $recipients, 'New announcement: ' . $title, 'announcement');

        echo json_encode([
            'success' => true,
            'announcement_id' => $announcementId,
            'sent' => count($recipients),
            'message' => 'Announcement published to ' . count($recipients) . ' recipient(s).',
        ]);
        exit;
    }

    if ($action === 'delete_announcement') {
        $announcementId = (int) ($payload['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE announcements SET status = "archived" WHERE id = ?');
        $stmt->execute([$announcementId]);

        echo json_encode(['success' => true, 'message' => 'Announcement archived.']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported request']);
