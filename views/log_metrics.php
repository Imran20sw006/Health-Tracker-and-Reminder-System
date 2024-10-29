<?php
session_start(); // Start the session at the very top

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include '../db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $blood_sugar_level = $_POST['blood_sugar_level'];
    $blood_pressure = $_POST['blood_pressure'];
    $medication_taken = isset($_POST['medication_taken']) ? 1 : 0;
    $log_date = $_POST['log_date'];
    $log_time = $_POST['log_time']; // Capture time input

    // Convert to 12-hour format
    $time_parts = explode(":", $log_time);
    $hour = (int)$time_parts[0];
    $minute = $time_parts[1];
    $period = $hour >= 12 ? 'PM' : 'AM'; // Determine AM/PM

    // Convert hour to 12-hour format
    $hour = $hour % 12;
    $hour = $hour ? $hour : 12; // The hour '0' should be '12'
    
    // Format time as 12-hour
    $formatted_time = sprintf('%02d:%02d:00 %s', $hour, $minute, $period);

    // Combine date and time into a single datetime string
    $log_datetime = $log_date . ' ' . $formatted_time;

    // Validation restrictions
    $errors = [];

    // Check if all fields are filled
    if (empty($blood_sugar_level) || empty($blood_pressure) || empty($log_date) || empty($log_time)) {
        $errors[] = "All fields are required.";
    }

    // Blood sugar range (normal range: 70 to 400 mg/dL)
    if ($blood_sugar_level < 70 || $blood_sugar_level > 400) {
        $errors[] = "Please enter a valid blood sugar level (70-400 mg/dL).";
    }

    // Blood pressure range (normal systolic: 60-200 mmHg)
    if ($blood_pressure < 60 || $blood_pressure > 200) {
        $errors[] = "Please enter a valid blood pressure (60-200 mmHg).";
    }

    // Log datetime should not be in the past
    if (strtotime($log_datetime) < time()) {
        $errors[] = "Log date and time cannot be in the past.";
    }

    // If no errors, insert into database and show alert
    if (empty($errors)) {
        $sql = "INSERT INTO health_metrics (user_id, blood_sugar_level, blood_pressure, medication_taken, log_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $user_id, $blood_sugar_level, $blood_pressure, $medication_taken, $log_datetime); // Use combined datetime

        if ($stmt->execute()) {
            // Check health metrics and suggest medication if needed
            $suggestion = '';
            // Blood sugar checks
            if ($blood_sugar_level < 80) {
                $suggestion .= "Your blood sugar is low. Consider eating something sweet or taking your medication.";
            } elseif ($blood_sugar_level > 200) {
                if ($medication_taken == 0) {
                    $suggestion .= "Your blood sugar is high. It is recommended to take your medication.";
                }
            }

            // Blood pressure checks
            if ($blood_pressure < 75) {
                $suggestion .= " Your blood pressure is low. Please consult with a healthcare provider.";
            } elseif ($blood_pressure > 130) {
                if ($medication_taken == 0) {
                    $suggestion .= " Your blood pressure is high. It is recommended to take your medication.";
                }
            }

            // Display suggestion if any
            if ($suggestion) {
                echo "<script>
                        setTimeout(function() {
                            alert('$suggestion');
                            window.location.href = 'dashboard.php';
                        }, 2000); // Wait for 2 seconds
                      </script>";
            } else {
                // If no suggestion, redirect to dashboard after 2 seconds
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'dashboard.php';
                        }, 2000); // Wait for 2 seconds
                      </script>";
            }
            exit();
        } else {
            echo "Error logging metrics.";
        }

        $stmt->close();
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Health Data</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 10px;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(45deg, #ff6b6b, #f7d94c, #6b6bff);
            background-size: 400% 400%;
            animation: gradient 10s ease infinite; /* Animation for background */
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h2 {
            text-align: center;
            color: #2c3e50; /* Darker shade for the heading for a professional look */
            margin-bottom: 30px;
            font-size: 28px; /* Increased font size for better visibility */
            width: 100%; /* Full width */
        }

        /* Form Container */
        form {
            max-width: 400px;
            width: 100%; /* Allow the form to take the full width */
            padding: 20px;
            margin: auto;
            border: 1px solid #bdc3c7; /* Subtle border color */
            border-radius: 12px;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white for form */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s, transform 0.3s; /* For hover effects */
            border: 3px solid #34495e;
        }

        /* Label Styles */
        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600; /* Bold for clarity */
            font-size: 14px;
            color: #34495e; /* Softer, professional color for labels */
        }

        /* Input Styles */
        input[type="number"],
        input[type="date"],
        input[type="time"],
        input[type="checkbox"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #bdc3c7; /* Subtle border for inputs */
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        /* Specific Checkbox Style */
        input[type="checkbox"] {
            width: auto; /* Checkbox is displayed inline */
            margin: 0; 
            margin-right: 10px; /* Space from label */
            vertical-align: middle;
        }

        /* Focus States for Inputs */
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus {
            border-color: #3498db; /* Highlight border on focus */
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
            outline: none; /* Remove default outline */
        }

        /* Submit Button Styles */
        input[type="submit"] {
            background-color: #3498db; /* Professional blue color for button */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s; /* Smooth transition */
            margin-top: 10px; /* Space above button */
            width: 100%; /* Full width button */
        }

        input[type="submit"]:hover {
            background-color: #2980b9; /* Darker shade on hover */
            transform: translateY(-2px); /* Lift effect on hover */
        }
    </style>
</head>
<body>
    <form action="log_metrics.php" method="POST">
    <h2>Enter Health Data</h2>
        <label for="blood_sugar_level">Blood Sugar Level (mg/dL):</label>
        <input type="number" name="blood_sugar_level" id="blood_sugar_level" required>

        <label for="blood_pressure">Blood Pressure (mmHg):</label>
        <input type="number" name="blood_pressure" id="blood_pressure" required>

        <label for="medication_taken">Medication Taken:</label>
        <input type="checkbox" name="medication_taken" id="medication_taken">
        <span>Yes, I took my medication</span>
        
        <label for="log_date">Enter Date:</label>
        <input type="date" name="log_date" id="log_date" required>
        
        <label for="log_time">Enter Time:</label>
        <input type="time" name="log_time" id="log_time" required>
        
        <input type="submit" value="Record Health Data">
    </form>
</body>
</html>
