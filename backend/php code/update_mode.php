<?php
// Database connection
$host = 'localhost'; // Change this to your database host
$user = 'phong_IOT'; // Change to your database username
$password = 'phongdeptrai'; // Change to your database password
$database = 'smart_plant_system';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get the JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// Extract plant_id and mode
$plant_id = $data['plant_id'];
$mode = $data['mode'];

// Update the mode in the database
$sql = "UPDATE plants_table SET mode = ? WHERE plant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $mode, $plant_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Mode updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update mode']);
}

$stmt->close();
$conn->close();
?>
