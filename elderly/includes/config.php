<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shohochor_db";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Don't expose database errors to users
    error_log("Database connection error: " . $e->getMessage());
    $conn = null;
}
?>