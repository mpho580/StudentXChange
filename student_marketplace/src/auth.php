<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Validate and normalise credentials before querying the database. */
function validateLoginCredentials($loginId, $password) {
    if (!is_string($loginId) || !is_string($password)) {
        return ['', '', 'Please submit the login form again.'];
    }

    $loginId = trim($loginId);
    if ($loginId === '') {
        return ['', '', 'Enter your email address or student number.'];
    }
    if (strlen($loginId) > 100) {
        return ['', '', 'Your email address or student number is too long.'];
    }

    if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
        $loginId = strtolower($loginId);
    } elseif (!preg_match('/^\d{7,20}$/', $loginId)) {
        return ['', '', 'Enter a valid email address or a 7–20 digit student number.'];
    }

    if ($password === '') {
        return ['', '', 'Enter your password.'];
    }
    if (strlen($password) < 6 || strlen($password) > 1024) {
        return ['', '', 'Enter a password between 6 and 1024 characters.'];
    }

    return [$loginId, $password, ''];
}

/** Authenticate a user and establish a fresh authenticated session. */
function login($emailOrStudentNum, $password) {
    global $conn;

    if (!is_string($emailOrStudentNum) || !is_string($password)) {
        return false;
    }

    $stmt = $conn->prepare(
        'SELECT id, student_number, name, email, password FROM users WHERE email = ? OR student_number = ? LIMIT 1'
    );
    if ($stmt === false) {
        error_log('Unable to prepare login query: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('ss', $emailOrStudentNum, $emailOrStudentNum);
    if (!$stmt->execute()) {
        error_log('Unable to execute login query: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    if ($result === false) {
        $stmt->close();
        return false;
    }
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        if ($update !== false) {
            $update->bind_param('si', $newHash, $user['id']);
            $update->execute();
            $update->close();
        }
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['student_number'] = $user['student_number'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    return true;
}

/** Log out the current user and destroy their session state. */
function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Check whether the session belongs to an authenticated user. */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT) !== false;
}

/** Redirect unauthenticated visitors to the login page. */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('Access restricted. Please log in first.', 'danger');
        redirect('/student_marketplace/public/login.php');
    }
}
