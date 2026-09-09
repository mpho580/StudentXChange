<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/messages.php';
require_once __DIR__ . '/../src/listings.php';

requireLogin();

$partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$partner = getUser($partner_id);
$product = getListing($product_id);

if (!$partner || !$product) {
    die("Conversation parameters are invalid.");
}

$messages = getConversation($_SESSION['user_id'], $partner_id, $product_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = sanitizeInput($_POST['message_text']);
    if (!empty($text)) {
        sendMessage($_SESSION['user_id'], $partner_id, $product_id, $text);
        redirect("thread.php?partner_id=$partner_id&product_id=$product_id");
    }
}

include __DIR__ . '/../public/header.php';
?>

<div class="row g-4 mt-1 justify-content-center">
    <div class="col-lg-8">
        <!-- Product Context -->
        <div class="card border-0 shadow-sm p-3 mb-3 bg-white rounded-3 d-flex flex-row align-items-center">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="rounded me-3 object-fit-cover" style="width: 50px; height: 50px;" onerror="this.src='../assets/default_product.png'">
            <div>
                <span class="text-secondary small d-block">Inquiry about:</span>
                <span class="fw-bold text-dark"><?php echo htmlspecialchars($product['title']); ?></span>
                <span class="text-success fw-bold ms-2"><?php echo formatPrice($product['price']); ?></span>
            </div>
            <a href="/student_marketplace/listing/view.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm ms-auto rounded-pill px-3">View Item</a>
        </div>
        
        <!-- Conversation Thread -->
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3 d-flex flex-column" style="height: 500px;">
            <div class="border-bottom pb-2 mb-3 d-flex align-items-center">
                <i class="bi bi-person-circle fs-4 text-primary me-2"></i>
                <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($partner['name']); ?></h5>
            </div>
            
            <div class="flex-grow-1 overflow-y-auto px-2 py-3 bg-light rounded-3 mb-3 d-flex flex-column gap-3" id="messageContainer">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-muted my-auto small">No messages yet. Send a greeting to discuss trade schedules!</div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): 
                        $isMe = ($msg['sender_id'] === $_SESSION['user_id']);
                    ?>
                        <div class="d-flex flex-column <?php echo $isMe ? 'align-items-end' : 'align-items-start'; ?>">
                            <div class="p-3 rounded-4 shadow-sm text-white max-w-75 <?php echo $isMe ? 'bg-primary' : 'bg-dark'; ?>" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($msg['message_text']); ?>
                            </div>
                            <span class="text-muted small mt-1" style="font-size: 0.75rem;"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message Input Form -->
            <form action="thread.php?partner_id=<?php echo $partner_id; ?>&product_id=<?php echo $product_id; ?>" method="POST" class="mt-auto">
                <div class="input-group">
                    <input type="text" class="form-control" name="message_text" placeholder="Type your offer or hand-off question..." required>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-send-fill"></i> Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto scroll messages to the bottom
var objDiv = document.getElementById("messageContainer");
if (objDiv) {
    objDiv.scrollTop = objDiv.scrollHeight;
}
</script>

<?php include __DIR__ . '/../public/footer.php'; ?>