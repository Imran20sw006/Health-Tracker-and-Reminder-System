<?php
session_start();
include '../db/db_connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Redirect to login page after successful reset
        header('Location: ../views/login.html');
        exit();
    } else {
        echo "Failed to reset password. Please check if the email is registered.";
    }

    $stmt->close();
    $conn->close();
}
?>


<?php
// reset_password.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Internal CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: flex-start; /* Align items to the left */
            align-items: center; /* Center items vertically */
            height: 100vh;
            background: url('../views/pexels-eberhard-grossgasteiger-1612351.jpg');
            background-size: cover; /* Ensures the image covers the entire screen */
        }

        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
            margin-left: 20px; /* Optional: Adds space from the left edge */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            color: white;
            margin-bottom: 10px;
            display: block;
            letter-spacing: 0.5px;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            background: rgba(0, 0, 0, 0.2); /* Darker background for visibility */
            color: #fff; /* White text for contrast */
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            background: rgba(232, 228, 228, 0); /* Slightly lighter on focus */
        }

        input[type="submit"], .back-btn {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #34d399, #3b82f6);
            color: black;
        }

        input[type="submit"]:hover {
            background: linear-gradient(135deg, #3b82f6, #34d399);
        }

        .back-btn {
            background-color: #FF7B72;
            color: white;
        }

        .back-btn:hover {
            background-color: #FF5A49;
        }

        p {
            text-align: center;
            font-size: 16px;
            margin-top: 20px;
            color: #fff;
        }

        /* Animations for more visual engagement */
        .form-container {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Reset Password</h2>
        <form action="reset_password_action.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <input type="submit" value="Reset Password">
            <button type="button" class="back-btn" onclick="window.location.href='../views/login.html'">Back</button> <!-- Back button -->
        </form>
    </div>
</body>
</html>

