<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/listings.php';
require_once __DIR__ . '/../src/csrf.php';

// Secure endpoint with authentication middleware
requireLogin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF Security Validation Failed.");
    }
    
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $condition = sanitizeInput($_POST['condition']);
    
    // Default mock image paths for demonstration
    $imagePath = '../assets/default_product.png';
    
    // File upload logic in standard server
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'png', 'jpeg', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../assets/uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagePath = '../assets/uploads/' . $newFileName;
            }
        }
    }
    
    if (empty($title) || empty($description) || $price <= 0 || $category_id <= 0 || empty($condition)) {
        $error = "Please fill in all listing fields correctly.";
    } else {
        $listingId = createListing($_SESSION['user_id'], $title, $description, $price, $category_id, $condition, $imagePath);
        if ($listingId) {
            setFlashMessage("Product listing published successfully!", "success");
            redirect('/student_marketplace/listing/view.php?id=' . $listingId);
        } else {
            $error = "Failed to upload the product listing.";
        }
    }
}

$csrf_token = generateCsrfToken();
include __DIR__ . '/../public/header.php';
?>

<div class="row justify-content-center mt-2">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            <h3 class="fw-bold mb-3 text-dark"><i class="bi bi-tag-fill text-warning me-2"></i>Publish An Item to Sell</h3>
            <p class="text-secondary small">Set up a clean listing card for fellow students on campus.</p>
            <hr class="mb-4">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="create.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                    <label for="title" class="form-label small fw-bold">Listing Title *</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Calculus 101 Pearson Textbook (12th Ed)" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label small fw-bold">Selling Price (ZAR) *</label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" placeholder="250.00" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label small fw-bold">Category *</label>
                        <select class="form-select text-dark" id="category_id" name="category_id" required>
                            <option value="1">Textbooks</option>
                            <option value="2">Electronics & Gadgets</option>
                            <option value="4">Apparel & Clothing</option>
                            <option value="3">Dorm Furniture</option>
                            <option value="5">Stationery</option>
                            <option value="8">Other Services</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="condition" class="form-label small fw-bold">Item Condition *</label>
                        <select class="form-select text-dark" id="condition" name="condition" required>
                            <option value="">Select Condition...</option>
                            <option value="New">Brand New</option>
                            <option value="Like New">Like New (barely used)</option>
                            <option value="Good">Good (minor scuffs)</option>
                            <option value="Fair">Fair (used heavily but working)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product_image" class="form-label small fw-bold">Product Picture</label>
                        <input class="form-control" type="file" id="product_image" name="product_image" accept="image/*">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label small fw-bold">Detailed Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Mention item state, meeting spot suggestions (e.g. Student Center Benches), or inclusions..." required></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="/student_marketplace/public/index.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-cloud-upload"></i> Publish Listing</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../public/footer.php'; ?>