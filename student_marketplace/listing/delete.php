<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/listings.php';   // ← add this line

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: /student_marketplace/public/my_listings.php");
    exit;
}

$id = (int)$_GET['id'];

$success = deleteListing($id, $_SESSION['user_id']);

if ($success) {
    // optional: set a flash message
    // setFlashMessage("Listing deleted successfully.", "success");
}

header("Location: /student_marketplace/public/my_listings.php");
exit;