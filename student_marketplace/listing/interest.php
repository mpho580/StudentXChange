<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/listings.php';
require_once __DIR__ . '/../src/messages.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid request.');
}

$productId = isset($_POST['product_id']) && ctype_digit((string) $_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;
$item = getListing($productId);
$buyerId = (int) $_SESSION['user_id'];

if (!$item || $item['status'] !== 'Available' || (int) $item['user_id'] === $buyerId) {
    setFlashMessage('This item is no longer available to express interest in.', 'warning');
    redirect('/student_marketplace/listing/view.php?id=' . $productId);
}

$message = "Hi, I'm interested in buying your item, \"{$item['title']}\". Is it still available?";
if (sendMessage($buyerId, (int) $item['user_id'], $productId, $message)) {
    setFlashMessage('Your interest was sent to the seller.', 'success');
} else {
    setFlashMessage('We could not notify the seller. Please try again.', 'danger');
}

redirect('/student_marketplace/message/thread.php?partner_id=' . (int) $item['user_id'] . '&product_id=' . $productId);
