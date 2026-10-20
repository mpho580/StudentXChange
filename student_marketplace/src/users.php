```php
<?php
require_once __DIR__ . '/db.php';

/**
 * Create/Register a New Student Account
 */
function createUser($studentNumber, $name, $surname, $email, $password, $phone) {
    global $conn;

    // Check if account already exists with same credentials
    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE email = ? OR student_number = ?"
    );
    $stmt->bind_param("ss", $email, $studentNumber);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return "Student number or Email is already registered.";
    }
    $stmt->close();

    // Secure Password Hashing with BCrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert student details, including surname
    $stmt = $conn->prepare(
        "INSERT INTO users
        (student_number, name, surname, email, password, phone)
        VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "ssssss",
        $studentNumber,
        $name,
        $surname,
        $email,
        $hashedPassword,
        $phone
    );

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        return $userId;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return "Registration failed: " . $error;
    }
}

/**
 * Fetch Student Profile Information
 */
function getUser($id) {
    global $conn;

    $stmt = $conn->prepare(
        "SELECT id, student_number, name, surname, email,
                phone, profile_picture, created_at
         FROM users
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    return $user;
}

/**
 * Update Student Profile Information
 */
function updateUser($id, $name, $surname, $phone, $profile_picture = null) {
    global $conn;

    if ($profile_picture) {
        $stmt = $conn->prepare(
            "UPDATE users
             SET name = ?, surname = ?, phone = ?, profile_picture = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "ssssi",
            $name,
            $surname,
            $phone,
            $profile_picture,
            $id
        );
    } else {
        $stmt = $conn->prepare(
            "UPDATE users
             SET name = ?, surname = ?, phone = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssi", $name, $surname, $phone, $id);
    }

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
?>
```