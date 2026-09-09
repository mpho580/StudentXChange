<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

requireLogin();
if (!isLoggedIn()) {
    redirect('/student_marketplace/public/login.php');
}
$userId = (int) $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('CSRF Security Validation Failed.');
    }

    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);

    if ($name === '') {
        $error = 'Your name is required.';
    } else {
        global $conn;

        $stmt = $conn->prepare(
            'UPDATE users SET name = ?, phone = ? WHERE id = ?'
        );
        $stmt->bind_param('ssi', $name, $phone, $userId);

        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            setFlashMessage('Your profile was updated successfully.', 'success');
            redirect('/student_marketplace/public/profile.php');
        }

        $error = 'We could not update your profile. Please try again.';
        $stmt->close();
    }
}

global $conn;
$stmt = $conn->prepare(
    'SELECT name, email, student_number, phone, created_at
     FROM users
     WHERE id = ?'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$initials = strtoupper(substr($user['name'], 0, 1));
$csrfToken = generateCsrfToken();

include __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="bg-primary text-white p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning text-dark fw-bold fs-3 d-flex align-items-center justify-content-center"
                         style="width: 72px; height: 72px;">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div>
                        <h2 class="h3 mb-1"><?= htmlspecialchars($user['name']) ?></h2>
                        <p class="mb-0 text-white-50">Student Marketplace Profile</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                   placeholder="e.g. 071 234 5678">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['student_number']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="border-top mt-4 pt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Member since <?= date('F Y', strtotime($user['created_at'])) ?>
                        </small>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>