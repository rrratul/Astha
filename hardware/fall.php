<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "health_monitor";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["fall"])) {
    $fall = $_POST["fall"];
    $sql = "INSERT INTO fall_data (fall_status) VALUES (" . $fall . ")";
    if (mysqli_query($conn, $sql)) {
        echo "Fall data saved successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "Error: Fall data not received";
}

mysqli_close($conn);
?>
