<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/config.php';

function isStrongPassword($password) {
    if (strlen($password) < 6) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

function isStudentEmail($email) {
    $email = strtolower(trim($email));
    return str_ends_with($email, '@my.richfield.ac.za');
}

function studentNumberMatchesEmail($studentNumber, $email) {
    $clean = preg_replace('/[^a-z0-9]/i', '', trim($studentNumber));
    $local = strtolower(strstr(trim($email), '@', true) ?: '');
    return $clean !== '' && strpos($local, strtolower($clean)) !== false;
}

try {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $studentNumber   = trim($data['student_number'] ?? '');
    $name            = trim($data['name'] ?? '');
    $surname         = trim($data['surname'] ?? '');
    $email           = trim($data['email'] ?? '');
    $phone           = trim($data['phone'] ?? '');
    $password        = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    // Required fields
    if ($studentNumber === '' || $name === '' || $surname === '' || $email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill in all mandatory fields.']);
        exit;
    }

    // Student number: numbers only
    if (!preg_match('/^[0-9]+$/', $studentNumber)) {
        echo json_encode(['success' => false, 'message' => 'Student number can only contain numbers.']);
        exit;
    }

    // Name / surname: letters only
    if (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
        echo json_encode(['success' => false, 'message' => 'Name can only contain letters (no numbers).']);
        exit;
    }
    if (!preg_match("/^[a-zA-Z\s'-]+$/", $surname)) {
        echo json_encode(['success' => false, 'message' => 'Surname can only contain letters (no numbers).']);
        exit;
    }

    // Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    if (!isStudentEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Only institutional student emails are allowed (e.g. @my.richfield.ac.za).']);
        exit;
    }

    // Student number must appear in email
    if (!studentNumberMatchesEmail($studentNumber, $email)) {
        echo json_encode(['success' => false, 'message' => 'Student number must match the number in your institutional email.']);
        exit;
    }

    // Phone (required, numbers only — same as website)
    if ($phone === '') {
        echo json_encode(['success' => false, 'message' => 'Cellphone number is required.']);
        exit;
    }
    if (!preg_match('/^[0-9]+$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number can only contain numbers.']);
        exit;
    }

    // Password
    if (!isStrongPassword($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters and contain 1 uppercase, 1 lowercase, 1 number and 1 special character.',
        ]);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Already registered?
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR student_number = ?');
    $stmt->bind_param('ss', $email, $studentNumber);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Student number or email is already registered.']);
        exit;
    }
    $stmt->close();

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $email  = strtolower($email);

    // Prefer name + surname columns; fallback if surname missing
    $stmt = $conn->prepare(
        "INSERT INTO users (student_number, name, surname, email, password, phone, role)
         VALUES (?, ?, ?, ?, ?, ?, 'student')"
    );

    if ($stmt) {
        $stmt->bind_param('ssssss', $studentNumber, $name, $surname, $email, $hashed, $phone);
    } else {
        $fullName = $name . ' ' . $surname;
        $stmt = $conn->prepare(
            "INSERT INTO users (student_number, name, email, password, phone, role)
             VALUES (?, ?, ?, ?, ?, 'student')"
        );
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param('sssss', $studentNumber, $fullName, $email, $hashed, $phone);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!',
            'user_id' => $stmt->insert_id,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}