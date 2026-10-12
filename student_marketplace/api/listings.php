<?php
require_once __DIR__ . '/config.php';

$search     = $_GET['search'] ?? '';
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$userId     = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null; // for "my listings"

$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN users u ON p.user_id = u.id
        WHERE 1=1";

$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}

if ($userId) {
    $sql .= " AND p.user_id = ?";
    $params[] = $userId;
    $types .= 'i';
}

$sql .= " ORDER BY p.created_at DESC LIMIT 50";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$listings = [];
while ($row = $result->fetch_assoc()) {
    // Make image URL work on the phone
    if (!empty($row['image_path']) && !str_starts_with($row['image_path'], 'http')) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $row['image_path'] = 'http://' . $host . '/student_marketplace/' . ltrim(str_replace('../', '', $row['image_path']), '/');
    }
    $listings[] = $row;
}

echo json_encode(['success' => true, 'listings' => $listings]);
$stmt->close();