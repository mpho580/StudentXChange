<?php
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid listing id']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT p.*, c.name AS category_name, u.name AS seller_name, u.id AS seller_id
     FROM products p
     JOIN categories c ON p.category_id = c.id
     JOIN users u ON p.user_id = u.id
     WHERE p.id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Listing not found']);
    exit;
}

if (!empty($item['image_path']) && !str_starts_with($item['image_path'], 'http')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $item['image_path'] = 'http://' . $host . '/student_marketplace/' . ltrim(str_replace('../', '', $item['image_path']), '/');
}

echo json_encode(['success' => true, 'listing' => $item]);