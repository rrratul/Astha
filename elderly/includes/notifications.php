<?php
// Check if file is being accessed directly
if (!defined('INCLUDED_IN_PAGE')) {
    // If accessed directly, include necessary files and start session
    session_start();
    require_once '../../../Astha/includes/config.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../../Astha/login_user/login_user.php");
        exit();
    }
}

// Get notifications
function getUnreadNotifications($user_id, $conn) {
    if (!$conn) return [];
    
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        AND is_read = 0 
        AND type != 'connection_request'
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    return $notifications;
}

function getConnectionRequests($user_id, $conn) {
    if (!$conn) return [];
    
    $stmt = $conn->prepare("
        SELECT rc.*, u.first_name, u.last_name, u.profile_picture, u.email, rc.relationship
        FROM relative_connections rc
        JOIN users u ON rc.family_member_id = u.id
        WHERE rc.elderly_id = ? AND rc.status = 'pending'
        ORDER BY rc.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    $stmt->close();
    return $requests;
}

// Only run this part if session and connection are available
if (isset($_SESSION['user_id']) && isset($conn)) {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get unread notifications
    $unread_notifications = getUnreadNotifications($user_id, $conn);
    
    // Get connection requests
    $connection_requests = getConnectionRequests($user_id, $conn);
    
    $total_notifications = count($unread_notifications) + count($connection_requests);
} else {
    // Default empty values when session or connection isn't available
    $unread_notifications = [];
    $connection_requests = [];
    $total_notifications = 0;
}
?>

<!-- Notification Icon for Nav Bar -->
<a href="<?php echo defined('INCLUDED_IN_PAGE') ? '../notifications.php' : 'notifications.php'; ?>" class="notification-link" aria-label="View notifications">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
    </svg>
    <?php if ($total_notifications > 0): ?>
        <span class="notification-badge"><?php echo $total_notifications; ?></span>
    <?php endif; ?>
</a>