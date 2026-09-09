<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authenticate Student User with Hashed Password Check
 */
function login($emailOrStudentNum, $password) {
    global $conn;
    
    // Support login by either Institutional Email or Student Number
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR student_number = ?");
    $stmt->bind_param("ss", $emailOrStudentNum, $emailOrStudentNum);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify BCrypt hashed password
        if (password_verify($password, $user['password'])) {
            // Prevent Session Fixation Attacks
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
    }
    return false;
}

/**
 * Log Out User and Destroy Session State
 */
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check if Session User is Authenticated
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Secure Page Middleware: Redirects unauthenticated users to Login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage("Access restricted. Please log in first.", "danger");
        redirect('/student_marketplace/public/login.php');
    }
}
?>