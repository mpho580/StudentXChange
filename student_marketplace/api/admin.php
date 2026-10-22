<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ---------- GET: stats / users / products ----------
if ($method === 'GET') {

    if ($action === 'stats' || $action === '') {
        $stats = [];
        $stats['total_users']    = (int)$conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
        $stats['total_products'] = (int)$conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
        $stats['total_messages'] = (int)$conn->query("SELECT COUNT(*) AS c FROM messages")->fetch_assoc()['c'];
        $stats['total_reviews']  = (int)$conn->query("SELECT COUNT(*) AS c FROM reviews")->fetch_assoc()['c'];

        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    if ($action === 'users') {
        $result = $conn->query(
            "SELECT id, student_number, name, email, phone, role, created_at
             FROM users ORDER BY created_at DESC"
        );
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    if ($action === 'products') {
        $sql = "SELECT p.*, u.name AS seller_name, c.name AS category_name
                FROM products p
                JOIN users u ON p.user_id = u.id
                JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";
        $result = $conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode(['success' => true, 'products' => $products]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown GET action.']);
    exit;
}

// ---------- POST: delete_user / delete_product / toggle_admin ----------
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $postAction = $data['action'] ?? '';
    $adminId    = (int)($data['admin_id'] ?? 0);

    // Require requester to be admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$admin || $admin['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Access denied.']);
        exit;
    }

    if ($postAction === 'delete_user') {
        $userId = (int)($data['user_id'] ?? 0);
        if ($userId === $adminId) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'User deleted.' : 'Delete failed.']);
        exit;
    }

    if ($postAction === 'delete_product') {
        $productId = (int)($data['product_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $productId);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Product deleted.' : 'Delete failed.']);
        exit;
    }

    if ($postAction === 'toggle_admin') {
        $userId = (int)($data['user_id'] ?? 0);
        if ($userId === $adminId) {
            echo json_encode(['success' => false, 'message' => 'You cannot change your own role.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }

        $newRole = ($user['role'] === 'admin') ? 'student' : 'admin';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param('si', $newRole, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Role updated.' : 'Update failed.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid method.']);
?>