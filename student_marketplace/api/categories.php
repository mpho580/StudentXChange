<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php';

try {
    $hidden = ['sport', 'accom'];
    $categories = [];

    $result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($row = $result->fetch_assoc()) {
        $skip = false;
        foreach ($hidden as $word) {
            if (stripos($row['name'], $word) !== false) {
                $skip = true;
                break;
            }
        }
        if (!$skip) {
            $categories[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }
    }

    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}