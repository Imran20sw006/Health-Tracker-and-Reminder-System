<?php
$servername = "localhost";
$username = "root";  // default XAMPP username
$password = "";      // leave blank for default XAMPP setup
$dbname = "health_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
