<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../src/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$loginId  = trim($data['login_id'] ?? '');
$password = $data['password'] ?? '';

if (empty($loginId) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email/Student number and password are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, student_number, name, surname, email, phone, password, role, profile_picture FROM users WHERE email = ? OR student_number = ?");
$stmt->bind_param("ss", $loginId, $loginId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        unset($user['password']);
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect email/student number or password."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Incorrect email/student number or password."]);
}

$stmt->close();
?>