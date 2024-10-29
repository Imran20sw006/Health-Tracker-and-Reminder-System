<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #e3f2fd; /* Light blue background */
    margin: 0;
    padding: 20px;
}

.dashboard-container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
    background-color: #ffffff; /* White background for container */
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.9);
    border: 2px solid black;
}

h2 {
    color: #2c3e50; /* Darker shade for the heading */
    text-align: center; /* Centered heading */
    margin-bottom: 20px; /* Space below heading */
    font-size: 28px; /* Increased font size */
}

p {
    text-align: center; /* Centered paragraph */
    font-size: 18px; /* Increased font size */
    color: #555; /* Dark gray color for text */
}

.action-btn {
    background-color: #28a745; /* Green button */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px; /* Rounded corners */
    cursor: pointer;
    margin: 5px 220px;
    text-decoration: none; /* For anchor tags */
    display: inline-block; /* For spacing */
    transition: background-color 0.3s, transform 0.2s; /* Smooth transitions */
    font-weight: bold;
    border: 2px solid black;

}

.action-btn:hover {
    background-color: #218838; /* Darker green on hover */
    transform: translateY(-1px); /* Lift effect on hover */
}

.metrics-table, .reminders-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0; /* Space above and below tables */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 2px solid black;

}

th, td {
    border: 1px solid #ddd; /* Light gray border for table cells */
    padding: 12px; /* Space inside table cells */
    text-align: left;
    border: 2px solid black;

}

th {
    background-color: #28a745; /* Green header */
    color: white; /* White text in header */
}

tr:nth-child(even) {
    background-color: #f9f9f9; /* Light gray for even rows */
}

tr:hover {
    background-color: #f1f1f1; /* Light gray on hover */
}

/* Alert Styles */
.alert {
    font-weight: bold;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0; /* Space above and below alerts */
}

.alert-blood-sugar {
    background-color: #e7f3fe; /* Light blue background */
    color: #31708f; /* Dark blue text */
}

.alert-blood-pressure {
    background-color: #f9ebea; /* Light pink background */
    color: #c0392b; /* Dark red text */
}

/* Section Titles */
.section-title {
    text-align: center;
    color: cyan; /* Bright text color */
    font-weight: bold;
    background-color: #282c34; /* Dark background for titles */
    padding: 20px;
    font-size: 36px; /* Large font size */
    margin: 20px 0; /* Space above and below section titles */
    border: 2px solid black;

}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 15px; /* Adjusted padding for smaller screens */
    }

    h2 {
        font-size: 24px; /* Smaller font size for smaller screens */
    }

    .action-btn {
        padding: 8px 12px; /* Adjusted padding for buttons */
    }
}



</style>
</head>
<body>
    <div class="dashboard-container">
    <h2>Dashboard</h2>
        <p>Welcome to your health dashboard!</p>
        <button class="action-btn" onclick="window.location.href='log_metrics.php'">Enter Health Data</button>
        <button class="action-btn" onclick="window.location.href='logout.php'">Logout</button>

        <?php
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        include '../db/db_connection.php';

        $user_id = $_SESSION['user_id'];

        // Display Health Metrics
        $sql = "SELECT * FROM health_metrics WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<h3>Your Health Metrics:</h3>";
            echo "<table class='metrics-table'>
                    <tr>
                        <th>Blood Sugar Level</th>
                        <th>Blood Pressure</th>
                        <th>Medication Taken</th>
                        <th>Entered Date & Time</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['blood_sugar_level']}</td>
                        <td>{$row['blood_pressure']}</td>
                        <td>" . ($row['medication_taken'] ? 'Yes' : 'No') . "</td>
                        <td>{$row['log_date']}</td>
                    </tr>";
            }
            echo "</table>";

            // Calculate Average Blood Sugar Level
            $sql_avg = "SELECT AVG(blood_sugar_level) AS avg_blood_sugar, AVG(blood_pressure) AS avg_blood_pressure FROM health_metrics WHERE user_id = ?";
            $stmt_avg = $conn->prepare($sql_avg);
            $stmt_avg->bind_param("i", $user_id);
            $stmt_avg->execute();
            $result_avg = $stmt_avg->get_result();
            $data_avg = $result_avg->fetch_assoc();
            $avg_blood_sugar = $data_avg['avg_blood_sugar'];
            $avg_blood_pressure = $data_avg['avg_blood_pressure'];
            
            echo "<div style='font-weight: bold; background-color: #e7f3fe; color: #31708f; font-size: 1.0em; padding: 10px; border-radius: 4px;     border: 2px solid black;
            '>Average Blood Sugar Level for this Month: " . number_format($avg_blood_sugar, 2) . " mg/dL</div>";
            echo "<div style='font-weight: bold; background-color: #f9ebea; color: #c0392b; font-size: 1.0em; padding: 10px; border-radius: 4px;     border: 2px solid black;
            '>Average Blood Pressure for this Month: " . number_format($avg_blood_pressure, 2) . " mmHg</div>";

            // Display Chart
            $sql_chart = "SELECT log_date, blood_sugar_level, blood_pressure, medication_taken FROM health_metrics WHERE user_id = ? ORDER BY log_date";
            $stmt_chart = $conn->prepare($sql_chart);
            $stmt_chart->bind_param("i", $user_id);
            $stmt_chart->execute();
            $result_chart = $stmt_chart->get_result();

            $dates = [];
            $blood_sugar_values = [];
            $blood_pressure_values = [];
            $blood_sugar_taken = [];
            $blood_pressure_taken = [];
            $blood_sugar_not_taken = [];
            $blood_pressure_not_taken = [];
            while ($row_chart = $result_chart->fetch_assoc()) {
                $dates[] = $row_chart['log_date'];
                $blood_sugar_values[] = $row_chart['blood_sugar_level'];
                $blood_pressure_values[] = $row_chart['blood_pressure'];

                // Separate values based on medication taken
                if ($row_chart['medication_taken']) {
                    $blood_sugar_taken[] = $row_chart['blood_sugar_level'];
                    $blood_pressure_taken[] = $row_chart['blood_pressure'];
                    $blood_sugar_not_taken[] = null; // Fill with null for consistency
                    $blood_pressure_not_taken[] = null; // Fill with null for consistency
                } else {
                    $blood_sugar_taken[] = null; // Fill with null for consistency
                    $blood_pressure_taken[] = null; // Fill with null for consistency
                    $blood_sugar_not_taken[] = $row_chart['blood_sugar_level'];
                    $blood_pressure_not_taken[] = $row_chart['blood_pressure'];
                }
            }
        } else {
            echo "<p>No health data found.</p>";
        }

        $stmt->close();

        // Display Reminders
        $sql_reminders = "SELECT id, reminder_type, reminder_time, status FROM reminders WHERE user_id = ? ORDER BY reminder_time ASC";
        $stmt_reminders = $conn->prepare($sql_reminders);
        $stmt_reminders->bind_param("i", $user_id);
        $stmt_reminders->execute();
        $result_reminders = $stmt_reminders->get_result();

        echo "<h3>Your Reminders:</h3>";
        if ($result_reminders->num_rows > 0) {
            echo "<table class='reminders-table'>
                    <tr>
                        <th>Reminder Type</th>
                        <th>Reminder Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>";
            while ($row_reminder = $result_reminders->fetch_assoc()) {
                echo "<tr>
                        <td>{$row_reminder['reminder_type']}</td>
                        <td>{$row_reminder['reminder_time']}</td>
                        <td>{$row_reminder['status']}</td>
                        <td>
                            <form action='' method='POST'>
                                <button type='button' style='background-color: #28a745; color: white; padding: 10px 15px; font-weight: bold; border: 2px solid black; border-radius: 10px; cursor: pointer;' onclick=\"window.location.href='../php/add_reminder.php'\">Add</button>
                                <button type='button' style='background-color: #28a745; color: white; padding: 10px 15px; font-weight: bold; border: 2px solid black; border-radius: 10px; cursor: pointer;' onclick=\"window.location.href='../php/edit_reminder.php?id={$row_reminder['id']}'\">Update</button>
                                <button type='button' style='background-color: #28a745; color: white; padding: 10px 15px; border: 2px solid black; font-weight: bold; border-radius: 10px; cursor: pointer;' onclick=\"if(confirm('Are you sure you want to delete this reminder?')) window.location.href='../php/delete_reminder.php?id={$row_reminder['id']}'\">Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<tr>
                    <td colspan='4'>No reminders found.</td>
                </tr>
                <tr>
                    <td colspan='4'>
                        <form action='' method='POST'>
                            <button type='button' style='background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer;' onclick=\"window.location.href='../php/add_reminder.php'\">Add</button>
                        </form>
                    </td>
                </tr>";
            echo "</table>";
        }

        $stmt_reminders->close();
        $conn->close();
        ?>

        <div style="text-align: center; color: cyan; font-weight: bold; background-color: #282c34; padding: 20px; font-size: 36px; border: 2px solid cyan; ">
            <br>Trends & Analysis<br>
        </div>

        <!-- Original Chart -->
        <div style="border: 2px solid black; border-radius: 10px; padding: 15px; background-color: #f9f9f9; max-width: 100%; margin: auto;">
    <canvas id="healthChart" width="400" height="200"></canvas>
</div>
        <script>
            // Draw the original chart
            const ctx = document.getElementById('healthChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [
                        {
                            label: 'Blood Sugar Levels',
                            data: <?php echo json_encode($blood_sugar_values); ?>,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            fill: false,
                        },
                        {
                            label: 'Blood Pressure Levels',
                            data: <?php echo json_encode($blood_pressure_values); ?>,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Level'
                            }
                        }
                    }
                }
            });
        </script>

<div style="text-align: center; color: cyan; font-weight: bold; background-color: #282c34; padding: 20px; font-size: 36px; border: 2px solid cyan;">
            <br>Medication Impact on Health<br>
        </div>

<!-- New Bar Chart for Medication Impact -->
<div style="border: 2px solid black; border-radius: 10px; padding: 15px; background-color: #f9f9f9; max-width: 100%; margin: auto;">
<canvas id="medicationChart" width="300" height="150"></canvas>
</div>
<script>
    // Draw the new bar chart
    const medicationCtx = document.getElementById('medicationChart').getContext('2d');
    new Chart(medicationCtx, {
        type: 'bar',
        data: {
            labels: ['Blood Sugar with Medication', 'Blood Sugar without Medication', 'Blood Pressure with Medication', 'Blood Pressure without Medication'],
            datasets: [{
                label: 'Levels',
                data: [
                    <?php echo array_sum($blood_sugar_taken); ?>, 
                    <?php echo array_sum($blood_sugar_not_taken); ?>, 
                    <?php echo array_sum($blood_pressure_taken); ?>, 
                    <?php echo array_sum($blood_pressure_not_taken); ?>
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 1)',  // Blood Sugar with Medication
                    'rgba(255, 99, 132, 1)',  // Blood Sugar without Medication
                    'rgba(54, 162, 235, 1)',   // Blood Pressure with Medication
                    'rgba(255, 206, 86, 1)'    // Blood Pressure without Medication
                ],
                borderColor: 'rgba(0, 0, 0, 1)', // Change to your desired border color
                borderWidth: 2, // Adjusted for a more subtle border
                barThickness: 100 // Set to medium size (adjust this value as needed)
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Levels',
                        font: {
                            size: 15, // Medium size for Y-axis title
                            weight: 'bold',
                            family: 'Arial', // You can change the font family if needed
                            color: 'black' // Color for the title
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Health Metrics',
                        font: {
                            size: 15, // Medium size for X-axis title
                            weight: 'bold',
                            family: 'Arial', // You can change the font family if needed
                            color: 'black' // Color for the title
                        }
                    },
                    ticks: {
                        font: {
                            size: 15, // Medium size for tick labels
                            weight: 'bold',
                            family: 'Arial', // You can change the font family if needed
                            color: 'black' // Color for the tick labels
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Health Metrics',
                    font: {
                        size: 16, // Size for the chart title
                        weight: 'bold',
                        family: 'Arial', // You can change the font family if needed
                        color: 'black' // Color for the title
                    }
                }
            }
        }
    });
</script>
<div>
</div>
    </div>
</body>
</html>
