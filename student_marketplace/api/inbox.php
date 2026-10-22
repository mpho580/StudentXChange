<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/config.php';

$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'user_id required']);
    exit;
}

$sql = "
SELECT
    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS partner_id,
    u.name AS partner_name,
    m.product_id,
    p.title AS product_title,
    MAX(m.sent_at) AS last_message
FROM messages m
JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
LEFT JOIN products p ON p.id = m.product_id
WHERE m.sender_id = ? OR m.receiver_id = ?
GROUP BY
    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END,
    m.product_id,
    u.name,
    p.title
ORDER BY last_message DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'conversations' => $conversations]);
?>