<?php
// Database connection
$host = 'localhost'; // Replace with your database host
$user = 'phong_IOT'; // Replace with your database username
$password = 'phongdeptrai'; // Replace with your database password
$database = 'smart_plant_system'; // Replace with your database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get the JSON payload from the frontend
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action']; // 'pump' or 'led'
$state = $data['state'];   // 'on' or 'off'

// Determine the current timestamp
$timestamp = date('Y-m-d H:i:s');

// Update the row for `sen_id = 1` (Pump)
if ($action === 'pump') {
    $sql = "UPDATE monitor 
            SET pump_state = '$state', time_stamp = '$timestamp' 
            WHERE sen_id = 1 
            ORDER BY monitor_id DESC 
            LIMIT 1";
    if ($conn->query($sql) !== TRUE) {
        die(json_encode(['success' => false, 'message' => 'Failed to update pump state']));
    }
}

// Update the row for `sen_id = 2` (Light)
if ($action === 'led') {
    $sql = "UPDATE monitor 
            SET light_state = '$state', time_stamp = '$timestamp' 
            WHERE sen_id = 2 
            ORDER BY monitor_id DESC 
            LIMIT 1";
    if ($conn->query($sql) !== TRUE) {
        die(json_encode(['success' => false, 'message' => 'Failed to update light state']));
    }
}

// Return a success response
echo json_encode(['success' => true, 'message' => 'Monitor table updated successfully']);

$conn->close();
?>
