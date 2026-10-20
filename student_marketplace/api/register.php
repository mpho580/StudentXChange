<?php
require_once __DIR__ . '/config.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId      = (int)($data['user_id'] ?? 0);
$title       = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$price       = floatval($data['price'] ?? 0);
$categoryId  = (int)($data['category_id'] ?? 0);
$condition   = trim($data['condition'] ?? '');
$imagePath   = '../assets/default_product.png';

if (!$userId || $title === '' || $description === '' || $price <= 0 || !$categoryId || $condition === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields correctly.']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO products (user_id, title, description, price, category_id, item_condition, image_path)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('issdiss', $userId, $title, $description, $price, $categoryId, $condition, $imagePath);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Listing published!', 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create listing.']);
}
$stmt->close();