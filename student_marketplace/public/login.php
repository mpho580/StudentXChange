<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    redirect('/student_marketplace/public/index.php');
}

$error = '';
$loginId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !verifyCsrfToken($submittedToken)) {
        $error = 'Security validation failed. Refresh the page and try again.';
    } else {
        $submittedLoginId = $_POST['login_id'] ?? '';
        $submittedPassword = $_POST['password'] ?? '';
        [$loginId, $password, $error] = validateLoginCredentials($submittedLoginId, $submittedPassword);

        if ($error === '' && !login($loginId, $password)) {
            // A generic message prevents account-enumeration attacks.
            $error = 'The email/student number or password is incorrect.';
        }

        if ($error === '') {
            unset($_SESSION['csrf_token']);
            setFlashMessage('Welcome back to StudentXChange!', 'success');
            redirect('/student_marketplace/public/index.php');
        }
    }
}

$csrfToken = generateCsrfToken();
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
    <main class="container" style="max-width: 450px;">
        <div class="text-center mb-4">
            <a class="text-decoration-none d-inline-block" href="/student_marketplace/public/index.php">
                <h1 class="text-primary fw-bold text-uppercase"><i class="bi bi-shop-window text-warning me-2"></i>StudentXChange</h1>
            </a>
            <p class="text-secondary small">Sign in to your campus account</p>
        </div>

        <section class="card border-0 shadow-lg p-4 bg-white rounded-3" aria-labelledby="login-heading">
            <h2 id="login-heading" class="visually-hidden">Sign in</h2>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="login_id" class="form-label small fw-bold text-dark">Email or student number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                        <input class="form-control" id="login_id" name="login_id" type="text" value="<?= htmlspecialchars($loginId, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 21908472 or study@varsity.edu" autocomplete="username" inputmode="email" maxlength="100" required>
                    </div>
                    <div class="form-text">Use a valid email address or your 7–20 digit student number.</div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold text-dark">Password</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" minlength="6" maxlength="1024" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mt-3"><i class="bi bi-box-arrow-in-right me-2"></i>Sign in</button>
            </form>
        </section>

        <p class="text-center text-secondary small mt-4">Don't have an account? <a href="register.php" class="text-primary fw-semibold">Register as a student</a></p>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
