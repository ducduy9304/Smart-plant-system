<?php
header('Content-Type: application/json');

// Connect to the database
include("config.php");

try {
    // Fetch plant details
    $sql_plants = "SELECT * FROM plants_table WHERE plant_id = 1";
    $result_plants = mysqli_query($conn, $sql_plants);
    $plants = array();
    $isPlantIdOne = false;

    // Check if plant_id == 1 exists
    while ($row = mysqli_fetch_assoc($result_plants)) {
        $plants[] = [
            'plant_id' => $row['plant_id'],
            'name' => $row['name'],
            'location' => $row['location'],
            'host' => $row['host'],
            'mode' => $row['mode']
        ];
        if ($row['plant_id'] == 1) {
            $isPlantIdOne = true; // Flag to allow fetching other data
        }
    }

    $response = [
        "plants" => $plants,
        "sensors" => null
    ];

    // Fetch sensor data only if plant_id == 1 exists
    if ($isPlantIdOne) {
        // Fetch the latest humidity value (sen_id = 1)
        $sql_humidity = "SELECT value, time_stamp FROM monitor WHERE sen_id = 1 ORDER BY time_stamp DESC LIMIT 1";
        $result_humidity = mysqli_query($conn, $sql_humidity);
        $humidity_data = mysqli_fetch_assoc($result_humidity);

        // Fetch the latest temperature value (sen_id = 2)
        $sql_temperature = "SELECT value, time_stamp FROM monitor WHERE sen_id = 2 ORDER BY time_stamp DESC LIMIT 1";
        $result_temperature = mysqli_query($conn, $sql_temperature);
        $temperature_data = mysqli_fetch_assoc($result_temperature);

        $response["sensors"] = [
            "humidity" => $humidity_data,
            "temperature" => $temperature_data
        ];
    }

    mysqli_close($conn);

    // Return the response
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
