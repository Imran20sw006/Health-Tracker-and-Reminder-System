<?php
session_start();
require_once '../db/db_connection.php'; // Database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Ensure the reminder ID is set
if (!isset($_GET['id'])) {
    echo "No reminder selected.";
    exit();
}

$reminder_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the values being submitted
    var_dump($_POST); // This will show the posted data
    $reminder_type = $_POST['reminder_type'];
    $reminder_time = $_POST['reminder_time'];

    // Validate the reminder time
    if (strtotime($reminder_time) < time()) {
        echo "Error: The reminder Date cannot be in the past.";
        exit();
    }

    // Update the reminder
    $sql = "UPDATE reminders SET reminder_type = ?, reminder_time = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $reminder_type, $reminder_time, $reminder_id, $user_id);

    if ($stmt->execute()) {
        header("Location: ../views/dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error; // Show any SQL errors
    }

    $stmt->close();
} else {
    // Fetch the current reminder data to display in the form
    $sql = "SELECT reminder_type, reminder_time FROM reminders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $reminder_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "No reminder found.";
        exit();
    }

    $reminder = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reminder</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(45deg, #ff6b6b, #f7d94c, #6b6bff);
            background-size: 300% 300%;
            animation: gradient 10s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h2 {
            color: #2c3e50; /* Darker shade for the heading */
            text-align: center; /* Centered heading */
            margin-bottom: 20px; /* Space below heading */
            font-size: 28px; /* Increased font size for visibility */
        }

        /* Form Container */
        form {
            background-color: rgba(255, 255, 255, 0.9); /* White background for form with some transparency */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px; /* Maximum width for the form */
            border: 2px solid #03a9f4; /* Light blue border */
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ff5722; /* Bright orange color for labels */
        }

        select, input[type="datetime-local"] {
            width: 100%; /* Set width to 100% to match button */
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #4caf50; /* Green border for inputs */
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s; /* Smooth transition for border color */
            box-sizing: border-box; /* Include padding and border in width */
        }

        /* Input Focus Effect */
        select:focus,
        input[type="datetime-local"]:focus {
            border-color: #ffeb3b; /* Yellow highlight border on focus */
            box-shadow: 0 0 5px rgba(255, 235, 59, 0.3); /* Yellow glow effect */
            outline: none; /* Remove default outline */
        }

        input[type="submit"] {
            background-color: #ff9800; /* Orange button */
            color: white; /* White text */
            padding: 12px 20px; /* Increased padding for the button */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 16px; /* Increased font size for the button */
            transition: background-color 0.3s, transform 0.2s; /* Smooth transitions */
            width: 100%; /* Full width button */
            font-weight: bold;
            border: 2px solid black;
        }

        input[type="submit"]:hover {
            background-color: #f57c00; /* Darker orange on hover */
            transform: translateY(-1px); /* Lift effect on hover */
        }

        /* Responsive Design */
        @media (max-width: 500px) {
            form {
                width: 90%; /* Adjust form width for smaller screens */
            }
        }
    </style>
    <script>
        function validateForm() {
            const reminderTime = new Date(document.getElementById('reminder_time').value);
            const now = new Date();
            if (reminderTime < now) {
                alert("The reminder date cannot be in the past.");
                return false; // Prevent form submission
            }
            return true; // Allow form submission
        }
    </script>
</head>
<body>
    <form action="edit_reminder.php?id=<?php echo $reminder_id; ?>" method="POST" onsubmit="return validateForm();">
        <h2>Update Reminder</h2>
        <label for="reminder_type">Reminder Type:</label>
        <select name="reminder_type" id="reminder_type" required>
            <option value="Medication" <?php if ($reminder['reminder_type'] == 'Medication') echo 'selected'; ?>>Medication</option>
            <option value="Appointment" <?php if ($reminder['reminder_type'] == 'Appointment') echo 'selected'; ?>>Appointment</option>
            <option value="Exercise" <?php if ($reminder['reminder_type'] == 'Exercise') echo 'selected'; ?>>Exercise</option>
        </select>

        <label for="reminder_time">Reminder Time:</label>
        <input type="datetime-local" name="reminder_time" id="reminder_time" value="<?php echo $reminder['reminder_time']; ?>" required>

        <input type="submit" value="Update Reminder">
    </form>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
