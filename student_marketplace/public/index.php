<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/listings.php';

include __DIR__ . '/header.php';

// Fetch recent active student listings from the database
$listings = getListings();
?>

<!-- Home Hero Jumbotron -->
<div class="p-5 mb-4 bg-primary text-white rounded-3 shadow-sm text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
    <div class="container-fluid py-3">
        <h1 class="display-5 fw-bold mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Campus Trading Made Effortless</h1>
        <p class="col-md-8 mx-auto fs-5 text-light-50">Exchange textbooks, dorm appliances, tech devices, or class notes in a verified student-only network. Safe hand-offs on campus, zero shipping costs, and maximum affordability.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/student_marketplace/public/register.php" class="btn btn-warning btn-lg fw-semibold px-4 py-2 mt-2 shadow-sm"><i class="bi bi-person-plus-fill me-2"></i>Get Registered</a>
        <?php else: ?>
            <a href="/student_marketplace/listing/create.php" class="btn btn-warning btn-lg fw-semibold px-4 py-2 mt-2 shadow-sm"><i class="bi bi-plus-circle-fill me-2"></i>Post an Item</a>
        <?php endif; ?>
    </div>
</div>

<!-- Browse Categories Section -->
<h4 class="mb-3 text-dark fw-bold"><i class="bi bi-grid-fill text-primary me-2"></i>Select Campus Category</h4>
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 mb-5">
    <?php
    $categories = [
        ['id' => 1, 'name' => 'Textbooks', 'icon' => 'bi-book-half'],
        ['id' => 2, 'name' => 'Electronics', 'icon' => 'bi-laptop'],
        ['id' => 3, 'name' => 'Apparel', 'icon' => 'bi-tag'],
        ['id' => 4, 'name' => 'Furniture', 'icon' => 'bi-house-door'],
        ['id' => 5, 'name' => 'Stationery', 'icon' => 'bi-pencil-fill'],
        ['id' => 6, 'name' => 'Services', 'icon' => 'bi-briefcase-fill']
    ];
    foreach ($categories as $cat):
    ?>
    <div class="col">
        <a href="/student_marketplace/public/search.php?category_id=<?php echo $cat['id']; ?>" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm text-center py-3 bg-white hover-up text-primary transition">
                <div class="card-body py-2">
                    <div class="fs-2 mb-2"><i class="bi <?php echo $cat['icon']; ?>"></i></div>
                    <span class="fw-semibold text-dark text-truncate d-block small"><?php echo $cat['name']; ?></span>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Listings Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Recently Uploaded Items</h4>
    <a href="/student_marketplace/public/search.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">Browse All</a>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    <?php if (empty($listings)): ?>
        <div class="col-12 text-center py-5">
            <div class="fs-1 text-muted"><i class="bi bi-shop"></i></div>
            <p class="text-secondary fs-5 mt-2">No items listed yet. Be the first to start the campus circular economy!</p>
        </div>
    <?php else: ?>
        <?php foreach (array_slice($listings, 0, 8) as $item): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm hover-up transition">
                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-2 py-1 fw-bold"><?php echo htmlspecialchars($item['item_condition']); ?></span>
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="card-img-top object-fit-cover" style="height: 180px;" alt="Product Image" onerror="this.src='../assets/default_product.png'">
                    <div class="card-body d-flex flex-column">
                        <span class="text-primary small fw-semibold"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        <h5 class="card-title text-dark fw-bold text-truncate mt-1 mb-1"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="card-text text-secondary small text-truncate-2 flex-grow-1"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="fs-5 fw-bold text-success"><?php echo formatPrice($item['price']); ?></span>
                            <a href="/student_marketplace/listing/view.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm px-3 rounded-pill">View Details</a>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-2 text-muted small d-flex justify-content-between">
                        <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($item['seller_name']); ?></span>
                        <span><i class="bi bi-calendar3"></i> <?php echo date('M d', strtotime($item['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>