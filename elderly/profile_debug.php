<?php

session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';

echo "<h2>Debug Information</h2>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>User ID from Session:</h3>";
echo $_SESSION['user_id'] ?? 'NOT SET';

// Get user data from database
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Database Query Result:</h3>";
    echo "Number of rows found: " . $result->num_rows . "<br>";
    
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        echo "<h3>Database Data:</h3>";
        echo "<pre>";
        print_r($user_data);
        echo "</pre>";
    } else {
        echo "No user found with ID: " . $_SESSION['user_id'];
    }
    $stmt->close();
} else {
    echo "No user_id in session";
}

// Check database structure
echo "<h3>Users Table Structure:</h3>";
$columns = $conn->query("DESCRIBE users");
echo "<table border='1'>";
echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $columns->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>