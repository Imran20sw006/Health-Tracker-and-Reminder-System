<?php
session_start();
include '../db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email is registered
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // If user with the email exists
    if ($user) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session and redirect to dashboard
            $_SESSION['user_id'] = $user['id'];
            header('Location: ../views/dashboard.php'); // Redirect to dashboard
            exit();
        } else {
            // Incorrect password
            echo "<script>alert('Incorrect password. Please try again!');</script>";
            echo "<script>window.location.href = '../views/login.html';</script>"; // Redirect back to login
            exit();
        }
    } else {
        // Email not registered
        echo "<script>alert('Email is not registered. Please sign up!');</script>";
        echo "<script>window.location.href = '../views/login.html';</script>"; // Redirect back to login
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
