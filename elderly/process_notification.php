<?php

session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['action']) || !isset($_POST['notification_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

$action = $_POST['action'];
$notification_id = (int)$_POST['notification_id'];
$user_id = $_SESSION['user_id'];

// Verify the notification belongs to this user
$verify_stmt = $conn->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
$verify_stmt->bind_param("ii", $notification_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification']);
    $verify_stmt->close();
    exit();
}

$verify_stmt->close();

// Process the action
if ($action === 'mark_read') {
    // Mark notification as read
    $update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $notification_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>