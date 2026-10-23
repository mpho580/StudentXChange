<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/listings.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);
$item = getListing($id);

if (!$item || (int)$item['user_id'] !== (int)$_SESSION['user_id']) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateListing(
        $id,
        (int)$_SESSION['user_id'],
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        (float)($_POST['price'] ?? 0),
        (int)($_POST['category_id'] ?? 0),
        $_POST['item_condition'] ?? 'Good'
    );

    header("Location: /student_marketplace/public/my_listings.php");
    exit;
}

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - StudentXChange</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8f9fa;
        }

        .edit-card {
            max-width: 680px;
            margin: 2.5rem auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.08);
            border: 1px solid rgba(0,0,0,.05);
            overflow: hidden;
        }

        .edit-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 1.5rem 2rem;
        }

        .edit-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .edit-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.4rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .btn-primary {
            background: #0d6efd;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #0a58ca;
        }

        .btn-outline-secondary {
            border-radius: 10px;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
        }

        .back-link {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="edit-card">
        <!-- Header -->
        <div class="edit-header d-flex justify-content-between align-items-center">
            <div>
                <a href="/student_marketplace/public/my_listings.php" class="back-link">
                    <i class="bi bi-arrow-left me-1"></i> Back to My Listings
                </a>
                <h1 class="mt-2">Edit Listing</h1>
            </div>
            <div class="d-none d-md-block">
                <i class="bi bi-pencil-square" style="font-size: 2.2rem; opacity: 0.3;"></i>
            </div>
        </div>

        <!-- Form -->
        <div class="edit-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($item['title']) ?>" required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($item['description']) ?></textarea>
                </div>

                <!-- Price + Category -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price (R)</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control"
                               value="<?= htmlspecialchars($item['price']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category ID</label>
                        <input type="number" name="category_id" class="form-control"
                               value="<?= htmlspecialchars($item['category_id']) ?>" required>
                    </div>
                </div>

                <!-- Condition -->
                <div class="mb-4">
                    <label class="form-label">Condition</label>
                    <select name="item_condition" class="form-select" required>
                        <?php
                        $conditions = ['New', 'Like New', 'Good', 'Fair', 'Poor'];
                        foreach ($conditions as $cond):
                        ?>
                            <option value="<?= $cond ?>" <?= $item['item_condition'] === $cond ? 'selected' : '' ?>>
                                <?= $cond ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                    <a href="/student_marketplace/public/my_listings.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>