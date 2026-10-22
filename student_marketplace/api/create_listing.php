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

    $userId      = (int)($data['user_id'] ?? 0);
    $title       = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $price       = floatval($data['price'] ?? 0);
    $categoryId  = (int)($data['category_id'] ?? 0);
    $condition   = trim($data['condition'] ?? '');
    $imageBase64 = $data['image_base64'] ?? null;
    $imageType   = $data['image_type'] ?? 'image/jpeg';

    if (!$userId || $title === '' || $description === '' || $price <= 0 || !$categoryId || $condition === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields correctly.']);
        exit;
    }

    $imagePath = null;

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

    $status = 'Available';

    $stmt = $conn->prepare(
        "INSERT INTO products
            (user_id, category_id, title, description, price, item_condition, image_path, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }

    $stmt->bind_param(
        'iissdsss',
        $userId,
        $categoryId,
        $title,
        $description,
        $price,
        $condition,
        $imagePath,
        $status
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Listing published!',
            'id' => $stmt->insert_id,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}