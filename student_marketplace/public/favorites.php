<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/favourites.php';
require_once __DIR__ . '/../src/listings.php';

requireLogin();

function redirectFromFavorites($path) {
    $default = '/student_marketplace/public/favorites.php';
    if (!is_string($path) || strpos($path, '/student_marketplace/') !== 0) {
        redirect($default);
    }
    redirect($path);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $returnTo = $_POST['return_to'] ?? '';

    if (!is_string($token) || !verifyCsrfToken($token) || !is_string($action) || !$productId) {
        setFlashMessage('The favourite request could not be verified. Please try again.', 'danger');
        redirectFromFavorites($returnTo);
    }

    $listing = getListing($productId);
    if (!$listing) {
        setFlashMessage('That listing is no longer available.', 'danger');
    } elseif ($action === 'add') {
        if ((int) $listing['user_id'] === (int) $_SESSION['user_id']) {
            setFlashMessage('You cannot save your own listing.', 'warning');
        } elseif (addFavorite((int) $_SESSION['user_id'], $productId)) {
            setFlashMessage('Listing saved to your favourites.', 'success');
        } else {
            setFlashMessage('The listing could not be saved. Please try again.', 'danger');
        }
    } elseif ($action === 'remove') {
        if (removeFavorite((int) $_SESSION['user_id'], $productId)) {
            setFlashMessage('Listing removed from your favourites.', 'success');
        } else {
            setFlashMessage('The listing could not be removed. Please try again.', 'danger');
        }
    } else {
        setFlashMessage('Unknown favourite action.', 'danger');
    }

    redirectFromFavorites($returnTo);
}

$favorites = getFavorites((int) $_SESSION['user_id']);
include __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">My favourites</h1>
        <p class="text-secondary mb-0">Listings you have saved for later.</p>
    </div>
    <a href="/student_marketplace/public/index.php" class="btn btn-outline-primary">Browse listings</a>
</div>

<?php if ($favorites === false): ?>
    <div class="alert alert-danger" role="alert">Your favourites could not be loaded. Please try again.</div>
<?php elseif ($favorites->num_rows === 0): ?>
    <div class="card border-0 shadow-sm text-center p-5">
        <i class="bi bi-heart fs-1 text-danger mb-3"></i>
        <h2 class="h4">No saved listings yet</h2>
        <p class="text-secondary mb-0">Use the heart button on a listing to save it here.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php while ($item = $favorites->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 border-0 shadow-sm">
                    <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5 card-title"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="fw-bold text-success mb-3"><?= formatPrice($item['price']) ?></p>
                        <div class="mt-auto d-flex gap-2">
                            <a class="btn btn-primary btn-sm" href="/student_marketplace/listing/view.php?id=<?= (int) $item['id'] ?>">View listing</a>
                            <form action="/student_marketplace/public/favorites.php" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="return_to" value="/student_marketplace/public/favorites.php">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
