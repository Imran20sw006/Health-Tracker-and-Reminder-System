<?php
session_start();
require_once '../db/db_connection.php'; // Database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Check if reminder ID is set
if (!isset($_GET['id'])) {
    echo "No reminder selected.";
    exit();
}

$reminder_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the reminder exists
$sql_check = "SELECT * FROM reminders WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $reminder_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    echo "No reminder found for this ID and user.";
    exit();
}

// Delete the reminder
$sql = "DELETE FROM reminders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $reminder_id, $user_id);

if ($stmt->execute()) {
    echo "Affected Rows: " . $stmt->affected_rows; // Debugging
    if ($stmt->affected_rows > 0) {
        header("Location: ../views/dashboard.php");
        ob_end_flush(); // Flush the output buffer
        exit();
    } else {
        echo "No reminder found or already deleted.";
    }
} else {
    echo "Error executing SQL: " . $stmt->error; // Display error if the deletion fails
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
