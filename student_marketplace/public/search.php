<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/listings.php';

include __DIR__ . '/header.php';

// Retrieve filtering parameters
$query = isset($_GET['query']) ? sanitizeInput($_GET['query']) : '';
$category_id = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? intval($_GET['category_id']) : null;
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$condition = isset($_GET['condition']) ? sanitizeInput($_GET['condition']) : null;

// Perform database query with filters
$results = getListings($query, $category_id, $min_price, $max_price, $condition);
?>

<div class="row g-4 mt-1">
    <!-- Filter Sidebar Column -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-funnel-fill text-primary me-2"></i>Filter Options</h5>
            <form action="search.php" method="GET">
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Category</label>
                    <select class="form-select text-dark" name="category_id">
                        <option value="">All Categories</option>
                        <option value="1" <?php if ($category_id == 1) echo 'selected'; ?>>Textbooks</option>
                        <option value="2" <?php if ($category_id == 2) echo 'selected'; ?>>Electronics & Gadgets</option>
                        <option value="3" <?php if ($category_id == 3) echo 'selected'; ?>>Apparel & Clothing</option>
                        <option value="4" <?php if ($category_id == 4) echo 'selected'; ?>>Dorm Furniture</option>
                        <option value="5" <?php if ($category_id == 5) echo 'selected'; ?>>Stationery</option>
                        <option value="6" <?php if ($category_id == 6) echo 'selected'; ?>>Services</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Price Range (R)</label>
                    <div class="row g-2">
                        <div class="col">
                            <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo $min_price; ?>">
                        </div>
                        <div class="col">
                            <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo $max_price; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Condition</label>
                    <select class="form-select text-dark" name="condition">
                        <option value="">Any Condition</option>
                        <option value="New" <?php if ($condition == 'New') echo 'selected'; ?>>New</option>
                        <option value="Like New" <?php if ($condition == 'Like New') echo 'selected'; ?>>Like New</option>
                        <option value="Good" <?php if ($condition == 'Good') echo 'selected'; ?>>Good</option>
                        <option value="Fair" <?php if ($condition == 'Fair') echo 'selected'; ?>>Fair</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary w-full mt-2"><i class="bi bi-check2-circle"></i> Apply Filters</button>
                <a href="search.php" class="btn btn-outline-secondary w-full mt-2 btn-sm">Clear Filters</a>
            </form>
        </div>
    </div>
    
    <!-- Results Column -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-dark fw-bold mb-0">
                <?php if (!empty($query)): ?>
                    Search Results for "<?php echo htmlspecialchars($query); ?>"
                <?php else: ?>
                    Campus Marketplace Board
                <?php endif; ?>
            </h4>
            <span class="badge bg-secondary rounded-pill px-3 py-2"><?php echo count($results); ?> Items Found</span>
        </div>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (empty($results)): ?>
                <div class="col-12 text-center py-5">
                    <div class="fs-1 text-muted mb-3"><i class="bi bi-search"></i></div>
                    <h5>No matching products found.</h5>
                    <p class="text-secondary small">Try broadening your search term or clearing filter parameters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($results as $item): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm hover-up transition">
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-2 py-1 fw-bold"><?php echo htmlspecialchars($item['item_condition']); ?></span>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="card-img-top object-fit-cover" style="height: 180px;" alt="Product" onerror="this.src='../assets/default_product.png'">
                            <div class="card-body d-flex flex-column">
                                <span class="text-primary small fw-semibold"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <h5 class="card-title text-dark fw-bold text-truncate mt-1 mb-1"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text text-secondary small text-truncate-2 flex-grow-1"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <span class="fs-5 fw-bold text-success"><?php echo formatPrice($item['price']); ?></span>
                                    <a href="/student_marketplace/listing/view.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm px-3 rounded-pill">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>