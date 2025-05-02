<?php
header('Content-Type: application/json');

// Connect to the database
include("config.php");

// Get the latest value and timestamp
$sql_latest = "SELECT value, time_stamp, pump_state FROM monitor ORDER BY time_stamp DESC LIMIT 1";
$result_latest = mysqli_query($conn, $sql_latest);
$current_data = mysqli_fetch_assoc($result_latest);

// Get the last 10 entries for the chart
$sql_chart = "SELECT value, time_stamp FROM monitor ORDER BY time_stamp DESC LIMIT 10";
$result_chart = mysqli_query($conn, $sql_chart);
$history_data = array();

while ($row = mysqli_fetch_assoc($result_chart)) {
    $history_data[] = $row;
}

mysqli_close($conn);

// Prepare JSON response
$response = [
    "current" => $current_data,
    "history" => $history_data
];

echo json_encode($response);
?>
