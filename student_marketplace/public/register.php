<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/users.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/csrf.php';

if (isLoggedIn()) {
    redirect('/student_marketplace/public/index.php');
}

$errors = [];
$old = [
    'student_number' => '',
    'name'           => '',
    'surname'        => '',
    'email'          => '',
    'phone'          => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("CSRF Security Validation Failed.");
    }

    $old['student_number'] = sanitizeInput($_POST['student_number'] ?? '');
    $old['name']           = sanitizeInput($_POST['name'] ?? '');
    $old['surname']        = sanitizeInput($_POST['surname'] ?? '');
    $old['email']          = sanitizeInput($_POST['email'] ?? '');
    $old['phone']          = sanitizeInput($_POST['phone'] ?? '');
    $password              = $_POST['password'] ?? '';
    $confirm_password      = $_POST['confirm_password'] ?? '';

    // VALIDATION

    //Student number validation
    if (empty($old['student_number'])) {
        $errors['student_number'] = 'Student number is required.';
    } elseif (!preg_match('/^[0-9]+$/', $old['student_number'])) {
        $errors['student_number'] = 'Student number can only contain numbers.';
    }

    // Name validation
    if (empty($old['name'])) {
        $errors['name'] = 'Name is required.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $old['name'])) {
        $errors['name'] = 'Name can only contain letters (no numbers).';
    }

    // Surname validation
    if (empty($old['surname'])) {
        $errors['surname'] = 'Surname is required.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $old['surname'])) {
        $errors['surname'] = 'Surname can only contain letters (no numbers).';
    }

    //Email validation
    if (empty($old['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (!isStudentEmail($old['email'])) {
        $errors['email'] = 'Only institutional student emails are allowed (e.g. @my.richfield.ac.za).';
    }

    //Student number and student email check
    if (empty($errors['student_number']) && empty($errors['email'])) {
        if (!studentNumberMatchesEmail($old['student_number'], $old['email'])) {
            $errors['student_number'] = 'Student number must match the number in your institutional email.';
            $errors['email']          = 'Your email must contain your student number.';
        }
    }

    // Phone numbers validation
    if (empty($old['phone'])) {
        $errors['phone'] = 'Cellphone number is required.';
    }else if (!empty($old['phone']) && !preg_match('/^[0-9]+$/', $old['phone'])) {
        $errors['phone'] = 'Phone number can only contain numbers.';
    }

    //Password validation
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (!isStrongPassword($password)) {
        $errors['password'] = 'Must be at least 6 characters and contain 1 uppercase, 1 lowercase, 1 number and 1 special character.';
    }

    //Confirm password validation
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // If no field errors → try to create the user
    if (empty($errors)) {
        $result = createUser(
            $old['student_number'],
            $old['name'],
            $old['surname'],
            $old['email'],
            $password,
            $old['phone']
        );

        if (is_numeric($result)) {
            $_SESSION['user_id']        = $result;
            $_SESSION['student_number'] = $old['student_number'];
            $_SESSION['name']           = $old['name'];
            $_SESSION['surname']        = $old['surname'];
            redirect('/student_marketplace/public/index.php');
        } else {
            if (stripos($result, 'email') !== false) {
                $errors['email'] = $result;
            } elseif (stripos($result, 'student') !== false) {
                $errors['student_number'] = $result;
            } else {
                $errors['general'] = $result;
            }
        }
    }
}

function isStrongPassword($password) {
    if (strlen($password) < 6) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

function isStudentEmail($email) {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $email = strtolower(trim($email));
    $allowedStudentDomains = ['@my.richfield.ac.za'];
    foreach ($allowedStudentDomains as $domain) {
        if (strpos($email, $domain) !== false) {
            return true;
        }
    }
    return false;
}

function studentNumberMatchesEmail($studentNumber, $email) {
    if (empty($studentNumber) || empty($email)) {
        return false;
    }

    $email = strtolower(trim($email));
    $studentNum = trim($studentNumber);
    $cleanStudentNum = preg_replace('/[^a-z0-9]/i', '', $studentNum);

    $localPart = strtolower(strstr($email, '@', true));
    if ($localPart === false) {
        return false;
    }

    return strpos($localPart, $cleanStudentNum) !== false;
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
        <h1 class="text-primary fw-bold text-uppercase">
            <i class="bi bi-shop-window text-warning me-2"></i>StudentXChange
        </h1>
        <p class="text-secondary small">Create a campus-verified peer-to-peer trading account</p>
    </div>
    
    <div class="card border-0 shadow-lg p-4 bg-white rounded-3">

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <!-- Student Number -->
            <div class="mb-3">
                <label for="student_number" class="form-label small fw-bold text-dark">Student Number *</label>
                <input type="text"
                       class="form-control <?= isset($errors['student_number']) ? 'is-invalid' : '' ?>"
                       id="student_number"
                       name="student_number"
                       value="<?= htmlspecialchars($old['student_number']) ?>"
                       required>
                <?php if (isset($errors['student_number'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['student_number']) ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label small fw-bold text-dark">Name *</label>
                    <input type="text"
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           id="name"
                           name="name"
                           value="<?= htmlspecialchars($old['name']) ?>"
                           pattern="[A-Za-z\s'-]+"
                           title="Only letters, spaces, hyphens and apostrophes are allowed"
                           inputmode="text"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Surname -->
                <div class="col-md-6 mb-3">
                    <label for="surname" class="form-label small fw-bold text-dark">Surname *</label>
                    <input type="text"
                           class="form-control <?= isset($errors['surname']) ? 'is-invalid' : '' ?>"
                           id="surname"
                           name="surname"
                           value="<?= htmlspecialchars($old['surname']) ?>"
                           pattern="[A-Za-z\s'-]+"
                           title="Only letters, spaces, hyphens and apostrophes are allowed"
                           inputmode="text"
                           required>
                    <?php if (isset($errors['surname'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['surname']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-dark">Institutional Email *</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>"
                       required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                <?php else: ?>
                    <div class="form-text text-muted" style="font-size: 0.75rem;">
                        Requires institutional university domains.
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Phone -->
            <div class="mb-3">
                <label for="phone" class="form-label small fw-bold text-dark">WhatsApp/Phone Number</label>
                <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone"
                       value="<?= htmlspecialchars($old['phone']) ?>"
                       pattern="[0-9]*"
                       inputmode="numeric"
                       title="Only numbers are allowed">
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php else: ?>
                    <div class="form-text text-muted" style="font-size: 0.75rem;">
                        Numbers only (optional).
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <!-- Password -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label small fw-bold text-dark">Password *</label>
                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label small fw-bold text-dark">Confirm Password *</label>
                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mt-3">
                Register Account
            </button>
        </form>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-secondary small">
            Already have a verified account?
            <a href="login.php" class="text-primary fw-semibold">Sign In</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
