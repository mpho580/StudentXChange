<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    redirect('/student_marketplace/public/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF security token
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF Security Validation Failed.");
    }
    
    $emailOrStudentNum = sanitizeInput($_POST['login_id']);
    $password = $_POST['password'];
    
    if (empty($emailOrStudentNum) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if (login($emailOrStudentNum, $password)) {
            setFlashMessage("Welcome back to StudentXChange!", "success");
            redirect('/student_marketplace/public/index.php');
        } else {
            $error = "Invalid institutional credentials or password.";
        }
    }
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudentXChange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

<div class="container" style="max-width: 450px;">
    <div class="text-center mb-4">
        <a class="text-decoration-none d-inline-block" href="/student_marketplace/public/index.php">
            <h1 class="text-primary fw-bold text-uppercase"><i class="bi bi-shop-window text-warning me-2"></i>StudentXChange</h1>
        </a>
        <p class="text-secondary small">Sign in to your verified campus account</p>
    </div>
    
    <div class="card border-0 shadow-lg p-4 bg-white rounded-3">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="mb-3">
                <label for="login_id" class="form-label small fw-bold text-dark">Email or Student Number</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="text" class="form-control" id="login_id" name="login_id" placeholder="e.g. 21908472 or study@varsity.edu" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label small fw-bold text-dark">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-full py-2 fw-semibold shadow-sm mt-3"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
        </form>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-secondary small">Don't have a verified account? <a href="register.php" class="text-primary fw-semibold">Register as a Student</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>