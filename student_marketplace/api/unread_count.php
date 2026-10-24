<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php';

try {
    $userId = (int)($_GET['user_id'] ?? 0);
    $since  = trim($_GET['since'] ?? '');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Missing user_id']);
        exit;
    }

    // Use the database clock so app and server never disagree
    $serverTime = $conn->query("SELECT NOW() AS t")->fetch_assoc()['t'];

    // First time on this phone: start counting from now
    if ($since === '' || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $since)) {
        echo json_encode(['success' => true, 'count' => 0, 'server_time' => $serverTime]);
        exit;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = ? AND created_at > ?");
    $stmt->bind_param('is', $userId, $since);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'success'     => true,
        'count'       => (int)($row['c'] ?? 0),
        'server_time' => $serverTime,
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}