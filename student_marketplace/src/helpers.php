<?php
/**
 * Sanitize User Input to Protect Against Cross-Site Scripting (XSS)
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Price to South African Rand (ZAR) or Local Currency
 */
function formatPrice($price) {
    return 'R ' . number_format($price, 2);
}

/**
 * Safely Redirect User to a Specific URL and Exit
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Display Alert Messages Saved in the Session
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show my-3" role="alert">';
        echo htmlspecialchars($msg);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Store a Flash Message in Session to display after Redirect
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}
?>