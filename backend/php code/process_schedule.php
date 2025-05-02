<?php
// Database connection
$servername = "localhost";
$username = "phong_IOT";
$password = "phongdeptrai";
$dbname = "smart_plant_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Current time
$current_day = date('l'); // Example: Sunday
$current_time = date('H:i:00'); // Example: 17:45:00

echo "<h1>Process Schedule</h1>";
echo "<p>Current Day: $current_day</p>";
echo "<p>Current Time: $current_time</p>";

// Query to fetch matching schedules
$sql = "
    SELECT * FROM schedule_table 
    WHERE (
        repeat_schedule = 'daily' OR 
        (repeat_schedule = 'custom' AND FIND_IN_SET('$current_day', custom_days) > 0)
    ) 
    AND scheduled_time = '$current_time'
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plantName = $row['plantName'];
        $action = $row['action_type'];
        echo "<p>Executing Action: $action for Plant: $plantName</p>";
    }
} else {
    echo "<p>No scheduled actions at this time.</p>";
}

$conn->close();
?>
<meta http-equiv="refresh" content="5">


