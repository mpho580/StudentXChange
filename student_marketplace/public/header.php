<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentXChange - Safe Campus Peer Marketplace</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/student_marketplace/public/index.php">
            <i class="bi bi-shop-window me-2 fs-3 text-warning"></i>
            <span class="fw-bold text-uppercase">Student<span class="text-warning">X</span>Change</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="studentNavbar">
            <!-- Global Search Form -->
            <form action="/student_marketplace/public/search.php" method="GET" class="d-flex mx-auto col-lg-5 col-12 my-2 my-lg-0">
                <div class="input-group">
                    <input class="form-control" type="search" name="query" placeholder="Search textbooks, laptops, dorm items..." required>
                    <button class="btn btn-warning" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="/student_marketplace/public/index.php">Home</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning text-dark px-3 py-1 mx-lg-2 my-1 my-lg-0 fw-semibold" href="/student_marketplace/listing/create.php">
                            <i class="bi bi-plus-circle-fill"></i> Sell Item
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student_marketplace/public/my_listings.php">My Listings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/student_marketplace/message/inbox.php">Inbox</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/student_marketplace/public/profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="/student_marketplace/public/favorites.php">Favorites</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/student_marketplace/public/logout.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form> 
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/student_marketplace/public/login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning text-dark px-3" href="/student_marketplace/public/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4 flex-grow-1">
    <?php displayFlashMessage(); ?>
