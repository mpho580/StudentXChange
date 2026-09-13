<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/listings.php';
require_once __DIR__ . '/../src/csrf.php';

include __DIR__ . '/../public/header.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
// Fetch structured product item
$item = getListing($id);

if (!$item) {
    echo '<div class="alert alert-danger text-center my-5"><h5>Product listing not found.</h5><a href="../public/index.php" class="btn btn-primary btn-sm mt-3">Back to Homepage</a></div>';
    include __DIR__ . '/../public/footer.php';
    exit();
}
?>

<div class="card border-0 shadow-sm p-4 bg-white rounded-3 mt-2">
    <div class="row g-4">
        <!-- Image Area -->
        <div class="col-md-5">
            <div class="position-relative">
                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-3 py-2 fw-bold fs-6 shadow-sm"><?php echo htmlspecialchars($item['item_condition']); ?></span>
                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="img-fluid rounded shadow-sm w-100 object-fit-cover" style="max-height: 400px;" alt="Product Card" onerror="this.src='../assets/default_product.png'">
            </div>
        </div>
        
        <!-- Details Panel -->
        <div class="col-md-7 d-flex flex-column">
            <div class="mb-3">
                <span class="badge bg-primary text-white mb-2"><?php echo htmlspecialchars($item['category_name']); ?></span>
                <h2 class="fw-bold text-dark"><?php echo htmlspecialchars($item['title']); ?></h2>
                <div class="text-muted small">Listed on <?php echo date('F d, Y @ h:i A', strtotime($item['created_at'])); ?></div>
            </div>
            
            <div class="mb-4 py-2 px-3 bg-light rounded d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small d-block">Price on Campus</span>
                    <span class="fs-2 fw-bold text-success"><?php echo formatPrice($item['price']); ?></span>
                </div>
                <div>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-shield-check me-1"></i>Verified Seller</span>
                </div>
            </div>
            
            <div class="mb-4">
                <h5 class="fw-bold text-dark">Seller Specifications</h5>
                <p class="text-secondary mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($item['description']); ?></p>
            </div>
            
            <div class="card border-primary bg-primary-subtle p-3 mb-4 rounded-3 mt-auto">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-3 text-primary me-2"></i>
                        <div>
                            <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($item['seller_name']); ?></span>
                            <span class="text-secondary small">Vetted Campus Peer</span>
                        </div>
                    </div>
                    <div>
                        <!-- Chat buttons -->
                        <?php if (isLoggedIn() && $_SESSION['user_id'] != $item['user_id']): ?>
                            <a href="/student_marketplace/message/thread.php?partner_id=<?php echo $item['user_id']; ?>&product_id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm px-3 fw-bold rounded-pill shadow-sm">
                                <i class="bi bi-chat-dots-fill me-1"></i> Message Seller
                            </a>
                            <form action="/student_marketplace/public/favorites.php" method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="return_to" value="<?= htmlspecialchars('/student_marketplace/listing/view.php?id=' . (int) $item['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-heart me-1"></i>Save</button>
                            </form>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="../public/login.php" class="btn btn-outline-primary btn-sm rounded-pill">Log in to Message</a>
                        <?php else: ?>
                            <span class="badge bg-secondary p-2 rounded-pill small">Your Listing</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../public/footer.php'; ?>
