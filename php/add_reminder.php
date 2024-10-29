<?php
session_start();
require_once '../db/db_connection.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reminder_type = $_POST['reminder_type'];
    $reminder_time = $_POST['reminder_time'];

    // Get the current time in the same format as the input (ISO format)
    $current_time = date('Y-m-d\TH:i');

    // Check if reminder time is in the past
    if ($reminder_time < $current_time) {
        echo "Error: Reminder time cannot be in the past.";
    } else {
        // Insert reminder into the database
        $sql = "INSERT INTO reminders (user_id, reminder_type, reminder_time) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $reminder_type, $reminder_time);

        if ($stmt->execute()) {
            // Redirect to view reminders after adding
            header('Location: ../views/dashboard.php');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reminder</title>
    <style>
/* General Styles */
body {
    font-family: 'Arial', sans-serif;
    padding: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    overflow: hidden;
    position: relative;
}

/* Animated Background */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(270deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1, #fad0c4, #ff9a9e, #a1c4fd, #c2e9fb);
    background-size: 800% 800%;
    z-index: -1;
    animation: gradientAnimation 25s ease infinite;
}

@keyframes gradientAnimation {
    0% { background-position: 0% 50%; }
    25% { background-position: 50% 100%; }
    50% { background-position: 100% 50%; }
    75% { background-position: 50% 0%; }
    100% { background-position: 0% 50%; }
}

/* Form Container */
form {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 240, 240, 0.9));
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 420px;
    border: 3px solid transparent;
    animation: formAnimation 1.8s ease-in-out infinite alternate; /* Color pulsing effect */
    position: relative;
    z-index: 1;
}

/* Form Animation */
@keyframes formAnimation {
    0% {
        border-color: #03a9f4;
        transform: translateY(0);
    }
    50% {
        border-color: #ff9800;
        transform: translateY(-5px);
    }
    100% {
        border-color: #8e44ad;
        transform: translateY(0);
    }
}

/* Form Labels */
label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
    color: #ff5722;
    animation: labelPulse 3s ease-in-out infinite; /* Color-changing animation */
}

/* Label Animation */
@keyframes labelPulse {
    0% { color: #ff5722; }
    50% { color: #03a9f4; }
    100% { color: #8e44ad; }
}

/* Input and Select Fields */
input[type="datetime-local"], select {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 2px solid #4caf50;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s, box-shadow 0.3s;
    box-sizing: border-box;
    background: rgba(255, 255, 255, 0.8);
    animation: inputAnimation 5s ease-in-out infinite; /* Subtle gradient effect */
}

/* Input Animation */
@keyframes inputAnimation {
    0% { background: rgba(255, 255, 255, 0.8); }
    50% { background: rgba(250, 250, 255, 0.9); }
    100% { background: rgba(255, 255, 255, 0.8); }
}

/* Input Focus Effect */
input[type="datetime-local"]:focus,
select:focus {
    border-color: #ffeb3b;
    box-shadow: 0 0 10px rgba(255, 235, 59, 0.6);
    outline: none;
}

/* Submit Button */
button {
    background-color: #ff9800;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
    width: 100%;
    font-weight: bold;
    animation: buttonPulse 2s ease-in-out infinite alternate; /* Color pulsing effect */
}

/* Button Animation */
@keyframes buttonPulse {
    0% { background-color: #ff9800; }
    100% { background-color: #f57c00; }
}

/* Button Hover Effect */
button:hover {
    background-color: #ff5722;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

/* Link Styles */
a {
    margin-top: 15px;
    display: inline-block;
    color: #03a9f4;
    text-decoration: none;
    text-align: center;
    font-weight: bold;
    animation: linkColorChange 4s ease-in-out infinite alternate; /* Color change animation */
}

/* Link Animation */
@keyframes linkColorChange {
    0% { color: #03a9f4; }
    50% { color: #ff9800; }
    100% { color: #8e44ad; }
}

/* Link Hover Effect */
a:hover {
    text-decoration: underline;
}

/* Responsive Design for Smaller Screens */
@media (max-width: 500px) {
    form {
        width: 90%;
    }
}
    </style>
</head>
<body>
    <form action="" method="POST">
    <h2>Add a New Reminder</h2>
        <label for="reminder_type">Reminder Type:</label>
        <select id="reminder_type" name="reminder_type" required>
            <option value="">Select a type</option>
            <option value="Appointment">Appointment</option>
            <option value="Medication">Medication</option>
            <option value="Exercise">Exercise</option>
        </select>
        
        <label for="reminder_time">Reminder Time:</label>
        <input type="datetime-local" id="reminder_time" name="reminder_time" required>
        
        <button type="submit">Add Reminder</button>
    </form>

    <script>
        // Set the minimum date and time for the reminder field to the current time
        document.addEventListener("DOMContentLoaded", function() {
            var reminderTimeField = document.getElementById('reminder_time');
            var now = new Date();
            var currentDateTime = now.toISOString().slice(0, 16); // Format as YYYY-MM-DDTHH:MM for datetime-local
            reminderTimeField.min = currentDateTime;
        });
    </script>
</body>
</html>
