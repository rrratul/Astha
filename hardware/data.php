<?php
// Database connection details
$hostname = "localhost";
$username = "root";
$password = "";
$database = "health_monitor";

// Create connection to the database
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the pulse and fall data are received via POST
if (isset($_POST["pulse"]) && isset($_POST["fall"])) {
    $pulse = $_POST["pulse"];
    $fall = $_POST["fall"];

    // Debug output to verify received values
    echo "Pulse: " . $pulse . "<br>";
    echo "Fall Status: " . $fall . "<br>";

    // Insert the pulse rate into the pulse_data table
    $sqlPulse = "INSERT INTO pulse_data (pulse_rate) VALUES (" . $pulse . ")";

    // Insert the fall status into the fall_data table
    $sqlFall = "INSERT INTO fall_data (fall_status) VALUES (" . $fall . ")";

    // Execute the queries and check for success
    if (mysqli_query($conn, $sqlPulse) && mysqli_query($conn, $sqlFall)) {
        echo "New records created successfully<br>";
    } else {
        echo "Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Error: Pulse or fall data not received<br>";
}

// Close the database connection
mysqli_close($conn);
?>
