<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

/**
 * Check if current user is an admin
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    global $conn;
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user && $user['role'] === 'admin';
}

/**
 * Protect admin pages
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlashMessage("Access denied. Admins only.", "danger");
        redirect('/student_marketplace/public/index.php');
    }
}

/**
 * Dashboard Statistics
 */
function getAdminStats() {
    global $conn;
    $stats = [];

    $stats['total_users']    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
    $stats['total_products'] = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
    $stats['total_messages'] = $conn->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];
    $stats['total_reviews']  = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];

    return $stats;
}

/**
 * Get all users
 */
function getAllUsers() {
    global $conn;
    $result = $conn->query("SELECT id, student_number, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

/**
 * Get all products with seller info
 */
function getAllProducts() {
    global $conn;
    $sql = "SELECT p.*, u.name as seller_name, u.email as seller_email, c.name as category_name 
            FROM products p 
            JOIN users u ON p.user_id = u.id 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

/**
 * Delete a user
 */
function adminDeleteUser($userId) {
    global $conn;
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        return "You cannot delete your own account.";
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();
    return $result ? true : "Failed to delete user.";
}

/**
 * Delete a product
 */
function adminDeleteProduct($productId) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Toggle admin / student role
 */
function toggleAdminStatus($userId) {
    global $conn;
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        return "You cannot change your own role.";
    }

    // Get current role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return "User not found.";
    }

    $newRole = ($user['role'] === 'admin') ? 'student' : 'admin';

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $userId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
?>