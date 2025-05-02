<?php
header('Content-Type: application/json');

// Connect to the database
include("config.php");

try {
    // Fetch the last 6 humidity entries (sen_id = 1)
    $sql_humidity = "SELECT value, time_stamp FROM monitor WHERE sen_id = 1 ORDER BY time_stamp DESC LIMIT 6";
    $result_humidity = mysqli_query($conn, $sql_humidity);
    $humidity_data = array();
    while ($row = mysqli_fetch_assoc($result_humidity)) {
        $humidity_data[] = $row;
    }

    // Fetch the last 6 temperature entries (sen_id = 2)
    $sql_temperature = "SELECT value, time_stamp FROM monitor WHERE sen_id = 2 ORDER BY time_stamp DESC LIMIT 6";
    $result_temperature = mysqli_query($conn, $sql_temperature);
    $temperature_data = array();
    while ($row = mysqli_fetch_assoc($result_temperature)) {
        $temperature_data[] = $row;
    }

    mysqli_close($conn);

    // Prepare JSON response
    $response = [
        "sensors" => [
            "humidity" => $humidity_data,
            "temperature" => $temperature_data
        ],
        
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
