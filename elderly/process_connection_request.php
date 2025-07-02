<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in and is elderly
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'elderly') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['action']) || !isset($_POST['connection_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

$action = $_POST['action'];
$connection_id = (int)$_POST['connection_id'];
$user_id = $_SESSION['user_id'];

// Verify the connection belongs to this user
$verify_stmt = $conn->prepare("SELECT id FROM relative_connections WHERE id = ? AND elderly_id = ?");
$verify_stmt->bind_param("ii", $connection_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid connection request']);
    $verify_stmt->close();
    exit();
}

$verify_stmt->close();

// Process the action
if ($action === 'accept') {
    // Update connection status to 'approved'
    $update_stmt = $conn->prepare("UPDATE relative_connections SET status = 'approved' WHERE id = ?");
    $update_stmt->bind_param("i", $connection_id);
    
    if ($update_stmt->execute()) {
        // Get the family member ID from the connection
        $get_family_member = $conn->prepare("SELECT family_member_id FROM relative_connections WHERE id = ?");
        $get_family_member->bind_param("i", $connection_id);
        $get_family_member->execute();
        $result = $get_family_member->get_result();
        $row = $result->fetch_assoc();
        $family_member_id = $row['family_member_id'];
        $get_family_member->close();
        
        // Create notification for family member
        $notification_title = "Connection Request Accepted";
        $notification_message = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) . 
                               " has accepted your connection request.";
        
        $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, reference_id) 
                                           VALUES (?, 'connection_accepted', ?, ?, ?)");
        $notification_stmt->bind_param("issi", $family_member_id, $notification_title, $notification_message, $connection_id);
        $notification_stmt->execute();
        $notification_stmt->close();
        
        $update_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Connection request accepted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to accept connection request']);
    }
} elseif ($action === 'reject') {
    // Update connection status to 'rejected'
    $update_stmt = $conn->prepare("UPDATE relative_connections SET status = 'rejected' WHERE id = ?");
    $update_stmt->bind_param("i", $connection_id);
    
    if ($update_stmt->execute()) {
        // Get the family member ID from the connection
        $get_family_member = $conn->prepare("SELECT family_member_id FROM relative_connections WHERE id = ?");
        $get_family_member->bind_param("i", $connection_id);
        $get_family_member->execute();
        $result = $get_family_member->get_result();
        $row = $result->fetch_assoc();
        $family_member_id = $row['family_member_id'];
        $get_family_member->close();
        
        // Create notification for family member
        $notification_title = "Connection Request Declined";
        $notification_message = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) . 
                               " has declined your connection request.";
        
        $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, reference_id) 
                                           VALUES (?, 'connection_rejected', ?, ?, ?)");
        $notification_stmt->bind_param("issi", $family_member_id, $notification_title, $notification_message, $connection_id);
        $notification_stmt->execute();
        $notification_stmt->close();
        
        $update_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Connection request rejected']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reject connection request']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>