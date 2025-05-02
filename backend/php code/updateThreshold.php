<?php
header('Content-Type: application/json');

// Connect to the database
include("config.php");

try {
    // Get the JSON payload from the POST request
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['humidity_min'], $input['humidity_max'], $input['temperature_min'], $input['temperature_max'])) {
        $humidity_min = $input['humidity_min'];
        $humidity_max = $input['humidity_max'];
        $temperature_min = $input['temperature_min'];
        $temperature_max = $input['temperature_max'];

        // Update the humidity thresholds
        $sql_humidity = "UPDATE sensors_table SET thres_min = ?, thres_max = ? WHERE type = 'humid'";
        $stmt_humidity = $conn->prepare($sql_humidity);
        $stmt_humidity->bind_param('ii', $humidity_min, $humidity_max);
        $stmt_humidity->execute();

        // Update the temperature thresholds
        $sql_temperature = "UPDATE sensors_table SET thres_min = ?, thres_max = ? WHERE type = 'temperature'";
        $stmt_temperature = $conn->prepare($sql_temperature);
        $stmt_temperature->bind_param('ii', $temperature_min, $temperature_max);
        $stmt_temperature->execute();

        // Check if updates were successful
        if ($stmt_humidity->affected_rows > 0 || $stmt_temperature->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No rows were updated.']);
        }

        $stmt_humidity->close();
        $stmt_temperature->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
