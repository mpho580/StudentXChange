<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/users.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    redirect('/student_marketplace/public/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF Security Validation Failed.");
    }
    
    $studentNumber = sanitizeInput($_POST['student_number']);
    $name = sanitizeInput($_POST['name']);
    $surname = sanitizeInput($_POST['surname']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Core Institutional Validations
    if (empty($studentNumber) || empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all mandatory fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Safe Registration CRUD
        $result = createUser($studentNumber, $name, $email, $password, $phone);
        if (is_numeric($result)) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = $result;
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
    <title>Register - StudentXChange</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center py-5 min-vh-100">

<div class="container" style="max-width: 500px;">
    <div class="text-center mb-4">
        <h1 class="text-primary fw-bold text-uppercase"><i class="bi bi-shop-window text-warning me-2"></i>StudentXChange</h1>
        <p class="text-secondary small">Create a campus-verified peer-to-peer trading account</p>
    </div>
    
    <div class="card border-0 shadow-lg p-4 bg-white rounded-3">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                <div class="mt-2"><a href="login.php" class="btn btn-sm btn-outline-success">Go to Login</a></div>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="mb-3">
                <label for="student_number" class="form-label small fw-bold text-dark">Student Number *</label>
                <input type="text" class="form-control" id="student_number" name="student_number" placeholder="" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label small fw-bold text-dark">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder=" " required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label small fw-bold text-dark">Surname *</label>
                    <input type="text" class="form-control" id="surname" name="surname" placeholder=" " required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-dark">Institutional Email *</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="" required>
                <div class="form-text text-muted" style="font-size: 0.75rem;">Requires institutional university domains.</div>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label small fw-bold text-dark">WhatsApp/Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label small fw-bold text-dark">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label small fw-bold text-dark">Confirm Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-full py-2 fw-semibold shadow-sm mt-3">Register Account</button>
        </form>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-secondary small">Already have a verified account? <a href="login.php" class="text-primary fw-semibold">Sign In</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
