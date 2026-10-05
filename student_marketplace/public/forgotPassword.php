
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

        $loginId = trim($_POST['login_id'] ?? '');

        if ($loginId === '') {

            $error = 'Please enter your email or student number.';

        } else {

            $user = findUserForPasswordReset($loginId);

            if (!$user) {

                $error = 'No account was found with those details.';

            } else {


                $_SESSION['password_reset_user_id'] = $user['id'];

                redirect('/student_marketplace/public/resetPassword.php');
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Forgot Password - StudentXChange</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/styles.css">

</head>


<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

<main class="container" style="max-width: 450px;">

    <div class="text-center mb-4">

        <a
            href="/student_marketplace/public/index.php"
            class="text-decoration-none"
        >

            <h1 class="text-primary fw-bold text-uppercase">

                <i class="bi bi-shop-window text-warning me-2"></i>

                StudentXChange

            </h1>

        </a>

        <p class="text-secondary small">
            Reset your password
        </p>

    </div>


    <section class="card border-0 shadow-lg p-4 bg-white rounded-3">

        <h2 class="h5 fw-bold mb-2">
            Forgot your password?
        </h2>

        <p class="text-secondary small mb-4">
            Enter your email address or student number to create a new password.
        </p>


        <?php if ($error !== ''): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <form action="forgotPassword.php" method="post">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $csrfToken,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >


            <div class="mb-3">

                <label
                    for="login_id"
                    class="form-label fw-semibold"
                >
                    Email or student number
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="login_id"
                    name="login_id"
                    value="<?= htmlspecialchars(
                        $loginId,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Enter your email or student number"
                    maxlength="100"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary w-100 py-2 fw-semibold"
            >

                <i class="bi bi-key-fill me-2"></i>

                Continue

            </button>

        </form>


        <div class="text-center mt-3">

            <a
                href="login.php"
                class="text-primary text-decoration-none small fw-semibold"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Back to login

            </a>

        </div>

    </section>

</main>

</body>

</html>

