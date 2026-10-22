<?php

require_once __DIR__ . "/../src/auth.php";
require_once __DIR__ . "/../src/listings.php";
require_once __DIR__ . "/../src/csrf.php";

requireLogin();

$id = isset($_POST['id']) && ctype_digit((string) $_POST['id']) ? (int) $_POST['id'] : 0;
$status = $_POST['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid request.');
}

setListingStatus($id, (int) $_SESSION['user_id'], $status);

header("Location: /student_marketplace/public/my_listings.php");
exit;
?>
