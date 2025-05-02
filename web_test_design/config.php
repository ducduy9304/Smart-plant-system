<?php
// Connect to database
$server = "localhost";
$user = "phong_IOT"; 
$pass = "phongdeptrai";
$dbname = "smart_plant_system";

$conn = mysqli_connect($server,$user,$pass,$dbname);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


?>