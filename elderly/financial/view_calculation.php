<?php
// filepath: c:\xampp\htdocs\Astha\elderly\financial\view_calculation.php
session_start();

// Make sure no output is sent before our JSON
ob_start();

// Include required files
require_once '../../login_user/functions.php';
require_once '../../signup_user/config.php';

// Authentication check
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Get calculation ID from request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid calculation ID']);
    exit();
}

// Fetch calculation from database
$stmt = $conn->prepare("SELECT * FROM financial_calculations WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Clear any output buffer
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

if ($row = $result->fetch_assoc()) {
    // Return calculation data as JSON
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Calculation not found']);
}
exit();
?>