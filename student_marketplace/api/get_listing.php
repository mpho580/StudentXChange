<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php'; // or your db connection

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();
$stmt->close();

if (!$listing) {
    echo json_encode(['success' => false, 'message' => 'Listing not found']);
    exit;
}

// Fix image URL for mobile (same logic as listings.php)
if (!empty($listing['image_path']) && !str_starts_with($listing['image_path'], 'http')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $listing['image_path'] = 'http://' . $host . '/student_marketplace/' . ltrim(str_replace('../', '', $listing['image_path']), '/');
}

echo json_encode(['success' => true, 'listing' => $listing]);