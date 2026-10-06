<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/admin.php';
require_once __DIR__ . '/../src/csrf.php';

requireLogin();
requireAdmin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete_user') {
        $result = adminDeleteUser((int)$_POST['user_id']);
        setFlashMessage(is_string($result) ? $result : "User deleted successfully.", is_string($result) ? "danger" : "success");
    }

    if ($action === 'delete_product') {
        adminDeleteProduct((int)$_POST['product_id']);
        setFlashMessage("Product deleted successfully.", "success");
    }

    if ($action === 'toggle_admin') {
        $result = toggleAdminStatus((int)$_POST['user_id']);
        setFlashMessage(is_string($result) ? $result : "Admin status updated.", is_string($result) ? "danger" : "success");
    }

    redirect('/student_marketplace/public/admin.php');
}

$stats    = getAdminStats();
$users    = getAllUsers();
$products = getAllProducts();
$csrf     = generateCsrfToken();
$tab      = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentXChange</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Your custom styles -->
    <link rel="stylesheet" href="../assets/styles.css">

    <style>
        /* Custom StudentXChange Styles extending Bootstrap */
        .hover-up {
            transition: transform 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
        }
        .hover-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08) !important;
        }
        .tracking-tight {
            letter-spacing: -0.025em;
        }
        .object-fit-cover {
            object-fit: cover;
        }
        .category-card {
            cursor: pointer;
            border-radius: 12px;
        }
        .product-card {
            border-radius: 12px;
        }
        .hero-banner {
            border-radius: 16px;
        }
        .transition {
            transition: all 0.2s ease-in-out;
        }

        /* ===== Admin Dashboard – Matching Home Page Colors ===== */
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8f9fa;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar – Primary Blue (same as hero) */
        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }

        .admin-sidebar .logo {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: #ffc107;          /* Bootstrap warning yellow */
            color: #212529;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .admin-nav a {
            display: block;
            padding: 0.8rem 1.5rem;
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .admin-nav a.active {
            border-left: 4px solid #ffc107;  /* yellow accent */
            background: rgba(255,255,255,0.18);
        }

        .admin-content {
            flex: 1;
            padding: 2rem;
            overflow-x: auto;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #0d6efd;               /* primary blue */
            margin-bottom: 0.25rem;
        }

        .stat-card p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Tables */
        .admin-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border-collapse: collapse;
            overflow: hidden;
        }

        .admin-table th,
        .admin-table td {
            padding: 0.9rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
        }

        .admin-table tr:hover {
            background: #f8f9fa;
        }

        /* Badges */
        .badge-admin {
            background: #ffc107;
            color: #212529;
            padding: 0.3rem 0.7rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-user {
            background: #e9ecef;
            color: #495057;
            padding: 0.3rem 0.7rem;
            border-radius: 50rem;
            font-size: 0.75rem;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="logo">
            <div class="logo-icon">S</div>
            <span>Student<span style="color: #ffc107;">XChange</span></span>
        </div>
        <nav class="admin-nav">
            <a href="?tab=dashboard" class="<?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="?tab=users" class="<?php echo $tab === 'users' ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Users
            </a>
            <a href="?tab=products" class="<?php echo $tab === 'products' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam me-2"></i> Products
            </a>
            <a href="/student_marketplace/public/index.php">
                <i class="bi bi-arrow-left me-2"></i> Back to Site
            </a>
            <a href="/student_marketplace/public/logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <h1 class="fw-bold mb-1 text-dark">Admin Dashboard</h1>
        <p class="text-secondary mb-4">Manage users, products and marketplace activity</p>

        <?php displayFlashMessage(); ?>

        <!-- DASHBOARD TAB -->
        <div class="tab-content <?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
            <div class="stats-grid">
                <div class="stat-card hover-up">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="stat-card hover-up">
                    <h3><?php echo $stats['total_products']; ?></h3>
                    <p>Active Listings</p>
                </div>
                <div class="stat-card hover-up">
                    <h3><?php echo $stats['total_messages']; ?></h3>
                    <p>Messages Sent</p>
                </div>
                <div class="stat-card hover-up">
                    <h3><?php echo $stats['total_reviews']; ?></h3>
                    <p>Reviews</p>
                </div>
            </div>

            <h4 class="fw-bold text-dark mb-3">
                <i class="bi bi-people-fill text-primary me-2"></i>Recent Users
            </h4>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Student No.</th>
                            <th>Email</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($users, 0, 5) as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- USERS TAB -->
        <div class="tab-content <?php echo $tab === 'users' ? 'active' : ''; ?>">
            <h4 class="fw-bold text-dark mb-3">
                <i class="bi bi-people-fill text-primary me-2"></i>All Users (<?php echo count($users); ?>)
            </h4>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Student No.</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge-admin">Admin</span>
                                <?php else: ?>
                                    <span class="badge-user">Student</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <div class="action-btns">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                                        <input type="hidden" name="action" value="toggle_admin">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <?php echo ($u['role'] === 'admin') ? 'Make Student' : 'Make Admin'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user and all their data?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUCTS TAB -->
        <div class="tab-content <?php echo $tab === 'products' ? 'active' : ''; ?>">
            <h4 class="fw-bold text-dark mb-3">
                <i class="bi bi-box-seam-fill text-primary me-2"></i>All Products (<?php echo count($products); ?>)
            </h4>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Seller</th>
                            <th>Condition</th>
                            <th>Listed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td class="fw-bold text-success">R <?php echo number_format($p['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['seller_name']); ?></td>
                            <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($p['item_condition']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>