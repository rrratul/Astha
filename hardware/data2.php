<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "health_monitor";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["fall"]) && isset($_POST["pulse"]) && isset($_POST["spo2"])) {
    $fall = $_POST["fall"];
    $pulse = $_POST["pulse"];
    $spo2 = $_POST["spo2"];

    // Insert fall data
    $sql_fall = "INSERT INTO fall_data (fall_status) VALUES ($fall)";
    if (!mysqli_query($conn, $sql_fall)) {
        echo "Error: " . $sql_fall . "<br>" . mysqli_error($conn);
    }

    // Insert pulse and SpO2 data
    $sql_pulse_oximeter = "INSERT INTO pulse_oximeter_data (pulse_rate, spo2) VALUES ($pulse, $spo2)";
    if (!mysqli_query($conn, $sql_pulse_oximeter)) {
        echo "Error: " . $sql_pulse_oximeter . "<br>" . mysqli_error($conn);
    } else {
        echo "Pulse and SpO2 data saved successfully";
    }
} else {
    echo "Error: Data not received";
}

mysqli_close($conn);
?>
