<?php
// Database connection details
$servername = "localhost";
$username = "phong_IOT";
$password = "phongdeptrai";
$dbname = "smart_plant_system";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Get form data
$plantName = isset($_GET['plantName']) ? $_GET['plantName'] : '';
$plant_location = isset($_GET['plant_location']) ? $_GET['plant_location'] : '';
$host = isset($_GET['host']) ? $_GET['host'] : '';
$scheduled_date = isset($_GET['scheduled_date']) ? $_GET['scheduled_date'] : '';
$scheduled_time = isset($_GET['scheduled_time']) ? $_GET['scheduled_time'] : '';
$action_type = isset($_GET['action']) ? implode(", ", $_GET['action']) : ''; // Combine selected actions
$repeat_schedule = isset($_GET['repeat_schedule']) ? $_GET['repeat_schedule'] : '';
// Get the `custom_days` array
$custom_days = '';
if (isset($_GET['repeat_schedule']) && $_GET['repeat_schedule'] === 'custom') {
    $custom_days = isset($_GET['custom_days']) ? implode(", ", $_GET['custom_days']) : '';
}

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO schedule_table (plantName, plant_location, host, action_type, scheduled_date, scheduled_time, repeat_schedule, custom_days)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $plantName, $plant_location, $host, $action_type, $scheduled_date, $scheduled_time, $repeat_schedule, $custom_days);

// Execute the query
if ($stmt->execute()) {
    echo "New schedule added successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Log all GET parameters for debugging
error_log(print_r($_GET, true));

// Close the connection
$stmt->close();
$conn->close();
?>
