
<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    redirect('/student_marketplace/public/index.php');
}

/*
 * Make sure the user came through forgotPassword.php.
 */
if (!isset($_SESSION['password_reset_user_id'])) {

    setFlashMessage(
        'Please start the password reset process again.',
        'danger'
    );

    redirect('/student_marketplace/public/login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !verifyCsrfToken($submittedToken)) {

        $error = 'Security validation failed. Refresh the page and try again.';

    } else {

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';


        /*
         * Check that both passwords were entered.
         */
        if ($newPassword === '' || $confirmPassword === '') {

            $error = 'Please enter and confirm your new password.';

        }

        /*
         * Check password length.
         */
        elseif (strlen($newPassword) < 6) {

            $error = 'Your new password must be at least 6 characters long.';

        }

        /*
         * Make sure both passwords match.
         */
        elseif ($newPassword !== $confirmPassword) {

            $error = 'The passwords do not match.';

        }

        else {

            $userId = $_SESSION['password_reset_user_id'];

            if (updateUserPassword($userId, $newPassword)) {

                /*
                 * Remove the reset session.
                 */
                unset($_SESSION['password_reset_user_id']);

                unset($_SESSION['csrf_token']);


                setFlashMessage(
                    'Your password has been changed successfully. You can now sign in.',
                    'success'
                );


                redirect('/student_marketplace/public/login.php');

            } else {

                $error = 'Unable to change your password. Please try again.';
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

    <title>Create New Password - StudentXChange</title>

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
            Create a new password
        </p>

    </div>


    <section class="card border-0 shadow-lg p-4 bg-white rounded-3">

        <h2 class="h5 fw-bold mb-2">
            Create a new password
        </h2>

        <p class="text-secondary small mb-4">
            Enter your new password below.
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


        <form action="resetPassword.php" method="post">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $csrfToken,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <!-- New Password -->

            <div class="mb-3">

                <label
                    for="new_password"
                    class="form-label fw-semibold"
                >
                    New password
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="new_password"
                    name="new_password"
                    minlength="6"
                    maxlength="1024"
                    autocomplete="new-password"
                    required
                >

                <div class="form-text">
                    Password must be at least 6 characters.
                </div>

            </div>

            <!-- Confirm Password -->

            <div class="mb-3">

                <label
                    for="confirm_password"
                    class="form-label fw-semibold"
                >
                    Confirm new password
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="6"
                    maxlength="1024"
                    autocomplete="new-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary w-100 py-2 fw-semibold"
            >

                <i class="bi bi-check-circle-fill me-2"></i>

                Create New Password

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

