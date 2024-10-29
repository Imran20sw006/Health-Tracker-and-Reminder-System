<?php
include '../db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate password length, alphanumeric, and special character
    if (strlen($password) < 8) {
        echo "<script>alert('Error: Password must be at least 8 characters long.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        echo "<script>alert('Error: Password must contain at least one letter.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/[0-9]/', $password)) {
        echo "<script>alert('Error: Password must contain at least one number.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        echo "<script>alert('Error: Password must contain at least one special character.'); window.history.back();</script>";
        exit();
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email already exists
    $checkEmailSql = "SELECT * FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Email already exists, send JavaScript alert
        echo "<script>alert('Error: This email is already registered. Please use a different email.'); window.history.back();</script>";
    } else {
        // Insert the new user
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password_hashed);

        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header('Location: ../views/login.html');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>
