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

global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('CSRF Security Validation Failed.');
    }

    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');

    if ($name === '') {

        $error = 'Your name is required.';

    } else {

        $newFilename = null;

        if (
            isset($_FILES['profile_picture']) &&
            $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES['profile_picture'];

            // Check upload error
            if ($file['error'] !== UPLOAD_ERR_OK) {

                $error = "There was a problem uploading the profile picture.";

            // Check file size
            } elseif ($file['size'] > 2 * 1024 * 1024) {

                $error = "Profile picture must be smaller than 2MB.";

            } else {

                // Allowed file extensions
                $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

                $ext = strtolower(
                    pathinfo($file['name'], PATHINFO_EXTENSION)
                );

                // Check if the file is actually an image
                $imageInfo = @getimagesize($file['tmp_name']);

                // Allowed MIME types
                $allowedMimes = ['image/jpeg','image/png','image/webp' ];

                if ( !in_array($ext, $allowedExt) ||$imageInfo === false ||
                    !in_array($imageInfo['mime'], $allowedMimes)
                ){
                     $error = "Only genuine JPG, PNG or WEBP images are allowed.";
                } else {

                    $uploadDir = __DIR__ . '/../assets/uploads/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $newFilename ='avatar_' .$userId .'_' .bin2hex(random_bytes(6)) .'.' .$ext;

                    if (
                        !move_uploaded_file(
                            $file['tmp_name'],
                            $uploadDir . $newFilename
                        )
                    ) {

                        $error ="Could not save the uploaded file. " .
                        "Please check that assets/uploads/ is writable.";
                         $newFilename = null;
                    }
                }
            }
        }

        if (empty($error)) {

            if ($newFilename) {

                $picturePath ='../assets/uploads/' . $newFilename;

                $stmt = $conn->prepare(
                    'UPDATE users
                     SET name = ?, phone = ?, profile_picture = ?
                     WHERE id = ?'
                );

                $stmt->bind_param('sssi',$name,$phone, $picturePath, $userId );

            } else {
                // No new picture selected
                $stmt = $conn->prepare(
                    'UPDATE users
                     SET name = ?, phone = ?
                     WHERE id = ?'
                );

                $stmt->bind_param(
                    'ssi',
                    $name,
                    $phone,
                    $userId
                );
            }

            if ($stmt->execute()) {

                $_SESSION['name'] = $name;

                setFlashMessage(
                    'Your profile was updated successfully.',
                    'success'
                );

                $stmt->close();
                redirect('/student_marketplace/public/profile.php');

            } else {

                $error ='We could not update your profile. Please try again.';
                $stmt->close();
            }
        }
    }
}

$stmt = $conn->prepare(
    'SELECT name,
            email,
            student_number,
            phone,
            profile_picture,
            created_at
     FROM users
     WHERE id = ?'
);

$stmt->bind_param('i', $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$stmt->close();

$avatarPath = $user['profile_picture'] ?? '';

$avatarExists =
    !empty($avatarPath) &&
    file_exists(__DIR__ . '/' . $avatarPath);

$initials = strtoupper(
    substr($user['name'], 0, 1)
);

$csrfToken = generateCsrfToken();

include __DIR__ . '/header.php';
?>


<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="bg-primary text-white p-4">
                <div class="d-flex align-items-center gap-3">
                    <?php if ($avatarExists): ?>
                        <img
                            src="<?= htmlspecialchars($avatarPath) ?>"
                            alt="Profile picture"
                            class="rounded-circle object-fit-cover"
                            style="width:72px; height:72px;"
                        >
                    <?php else: ?>
                        <div
                            class="rounded-circle bg-warning text-dark fw-bold fs-3 d-flex align-items-center justify-content-center"
                            style="width:72px; height:72px;"
                        >
                            <?= htmlspecialchars($initials) ?>
                        </div>

                    <?php endif; ?>

                    <div>

                        <h2 class="h3 mb-1">
                            <?= htmlspecialchars($user['name']) ?>
                        </h2>

                        <p class="mb-0 text-white-50">
                            Student Marketplace Profile
                        </p>

                    </div>

                </div>

            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken) ?>"
                    >

                    <div class="mb-4">

                        <label
                            for="profile_picture"
                            class="form-label fw-semibold"
                        >
                            Profile Picture
                        </label>


                        <div class="d-flex align-items-center gap-3">


                            <?php if ($avatarExists): ?>

                                <img
                                    src="<?= htmlspecialchars($avatarPath) ?>"
                                    alt="Current profile picture"
                                    class="rounded-circle border object-fit-cover"
                                    style="width:96px; height:96px;"
                                >

                            <?php else: ?>

                                <divclass="rounded-circle border bg-light d-flex align-items-center justify-content-center"
                                    style="width:96px; height:96px;"
                                >
                                    <i
                                        class="bi bi-person-fill text-secondary"
                                        style="font-size:2.5rem;"
                                    ></i>

                                </div>

                            <?php endif; ?>

                            <div class="flex-grow-1">

                                <input type="file" class="form-control" id="profile_picture"
                                    name="profile_picture"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >
                                <div class="form-text">
                                    JPG, PNG or WEBP. Maximum size: 2MB.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">
                                Full Name
                            </label>

                            <input type="text" class="form-control" id="name"
                                name="name" value="<?= htmlspecialchars($user['name']) ?>"required>
                        </div>
                        <div class="col-md-6">
                            <label
                                for="phone"class="form-label fw-semibold" >
                                Phone Number
                            </label>

                            <input
                                type="tel"
                                class="form-control"
                                id="phone"
                                name="phone"
                                value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                placeholder="e.g. 071 234 5678" >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Student Number
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="<?= htmlspecialchars($user['student_number']) ?>"
                                readonly>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                value="<?= htmlspecialchars($user['email']) ?>"
                                readonly >

                        </div>

                    </div>

                    <div class="border-top mt-4 pt-3 d-flex justify-content-between align-items-center">

                        <small class="text-muted">
                            Member since
                            <?= date(
                                'F Y',
                                strtotime($user['created_at'])
                            ) ?>

                        </small>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
```
