<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $userId    = (int)($_GET['user_id'] ?? 0);
        $partnerId = (int)($_GET['partner_id'] ?? 0);
        $productId = (int)($_GET['product_id'] ?? 0);

        if (!$userId || !$partnerId) {
            echo json_encode(['success' => false, 'message' => 'Missing user_id or partner_id']);
            exit;
        }

        $sql = "
            SELECT m.id, m.sender_id, m.receiver_id, m.product_id,
                   m.message AS body, m.sent_at AS created_at,
                   u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (
                (m.sender_id = ? AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = ?)
            )
        ";

        if ($productId > 0) {
            $sql .= " AND (m.product_id = ? OR m.product_id = 0 OR m.product_id IS NULL)";
            $sql .= " ORDER BY m.sent_at ASC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => $conn->error]);
                exit;
            }
            $stmt->bind_param('iiiii', $userId, $partnerId, $partnerId, $userId, $productId);
        } else {
            $sql .= " ORDER BY m.sent_at ASC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => $conn->error]);
                exit;
            }
            $stmt->bind_param('iiii', $userId, $partnerId, $partnerId, $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();

        echo json_encode(['success' => true, 'messages' => $messages]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $senderId   = (int)($data['sender_id'] ?? 0);
        $receiverId = (int)($data['receiver_id'] ?? 0);
        $productId  = (int)($data['product_id'] ?? 0);
        $body       = trim($data['body'] ?? '');

        if (!$senderId || !$receiverId || $body === '') {
            echo json_encode(['success' => false, 'message' => 'Missing fields']);
            exit;
        }

        $stmt = $conn->prepare(
            "INSERT INTO messages (sender_id, receiver_id, product_id, message, sent_at, is_read)
             VALUES (?, ?, ?, ?, NOW(), 0)"
        );
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => $conn->error]);
            exit;
        }
        $stmt->bind_param('iiis', $senderId, $receiverId, $productId, $body);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid method']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>