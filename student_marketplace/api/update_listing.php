<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $id          = (int)($data['id'] ?? 0);
    $userId      = (int)($data['user_id'] ?? 0);
    $title       = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $price       = floatval($data['price'] ?? 0);
    $categoryId  = (int)($data['category_id'] ?? 0);
    $condition   = trim($data['condition'] ?? $data['item_condition'] ?? '');
    $imageBase64 = $data['image_base64'] ?? null;
    $imageType   = $data['image_type'] ?? 'image/jpeg';

    $allowedConditions = ['New', 'Like New', 'Good', 'Fair', 'Poor'];

    if (!$id || !$userId || $title === '' || $description === '' || $price <= 0 || !$categoryId) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields correctly.']);
        exit;
    }
    if (!in_array($condition, $allowedConditions, true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid item condition.']);
        exit;
    }

    // Make sure the listing exists and belongs to this user
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'Listing not found or not yours.']);
        exit;
    }

    $imagePath = $existing['image_path'];

    // Only replace the image if a new one was sent
    if (!empty($imageBase64)) {
        $bin = base64_decode($imageBase64, true);
        if ($bin === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid image data.']);
            exit;
        }
        if (strlen($bin) > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image must be smaller than 5MB.']);
            exit;
        }

        $ext = 'jpg';
        if (str_contains($imageType, 'png')) $ext = 'png';
        elseif (str_contains($imageType, 'webp')) $ext = 'webp';

        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        if (file_put_contents($uploadDir . $newName, $bin) === false) {
            echo json_encode(['success' => false, 'message' => 'Could not save image.']);
            exit;
        }

        $imagePath = '../assets/uploads/' . $newName;
    }

    $stmt = $conn->prepare(
        "UPDATE products
         SET title = ?, description = ?, price = ?, category_id = ?, item_condition = ?, image_path = ?
         WHERE id = ? AND user_id = ?"
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }

    $stmt->bind_param('ssdissii', $title, $description, $price, $categoryId, $condition, $imagePath, $id, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Listing updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}