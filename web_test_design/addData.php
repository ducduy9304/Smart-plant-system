<?php
// Read data from POST request
$threshold_min = $_POST["threshold_min"];
$threshold_max = $_POST["threshold_max"];
$sen_id = $_POST["sen_id"]; // Assuming sen_id is passed to identify the sensor

// Connect to the database
include("config.php");

// Prepare and execute the SQL statement
$sql = "UPDATE sensors_table SET thres_min = '$threshold_min', thres_max = '$threshold_max' WHERE sen_id = '$sen_id'";
if (mysqli_query($conn, $sql)) {
    echo "Threshold values updated successfully";
} else {
    echo "Error updating thresholds: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>

