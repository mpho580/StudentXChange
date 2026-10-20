<?php
// Database Configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "student_marketplace";

// Create Connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set Charset to Support Special Characters
$conn->set_charset("utf8mb4");
?>