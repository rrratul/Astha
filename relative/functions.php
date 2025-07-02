<?php

/**
 * Convert a timestamp to a human-readable "time ago" format
 * 
 * @param string $datetime MySQL datetime string
 * @param bool $full Whether to show the full date or just "ago" format
 * @return string Formatted time string
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Format a date according to the preferred format
 * 
 * @param string $date MySQL date or datetime string
 * @param string $format PHP date format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Formats a datetime for display with time
 * 
 * @param string $datetime MySQL datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    return date('M d, Y - h:i A', strtotime($datetime));
}

/**
 * Get user information by user ID
 * 
 * @param int $user_id User ID
 * @return array|false User data or false if not found
 */
function get_user($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

/**
 * Get full name of user
 * 
 * @param int $user_id User ID
 * @return string User's full name
 */
function get_user_name($user_id) {
    $user = get_user($user_id);
    if ($user) {
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    return 'Unknown User';
}

/**
 * Check if a user has unread notifications
 * 
 * @param int $user_id User ID
 * @return int Number of unread notifications
 */
function get_unread_notification_count($user_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

/**
 * Get recent notifications for a user
 * 
 * @param int $user_id User ID
 * @param int $limit Maximum number of notifications to return
 * @return mysqli_result Result set with notifications
 */
function get_recent_notifications($user_id, $limit = 5) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Mark a notification as read
 * 
 * @param int $notification_id Notification ID
 * @return bool True if successful, false otherwise
 */
function mark_notification_read($notification_id) {
    global $conn;
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

/**
 * Send a notification to a user
 * 
 * @param int $user_id User ID
 * @param string $type Notification type
 * @param string $message Notification message
 * @param string $link Link to redirect to when clicked (optional)
 * @return bool True if successful, false otherwise
 */
function send_notification($user_id, $type, $message, $link = '') {
    global $conn;
    $sql = "INSERT INTO notifications (user_id, type, message, link, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $type, $message, $link);
    return $stmt->execute();
}

/**
 * Calculate age from birthdate
 * 
 * @param string $birthdate Birthdate in MySQL date format
 * @return int Age in years
 */
function calculate_age($birthdate) {
    return date_diff(date_create($birthdate), date_create('today'))->y;
}

/**
 * Sanitize and validate input
 * 
 * @param string $input Input string to sanitize
 * @param string $type Type of validation (text, email, etc.)
 * @return string Sanitized input
 */
function sanitize_input($input, $type = 'text') {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    
    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            break;
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            break;
        case 'int':
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
    }
    
    return $input;
}

/**
 * Check if family member is connected to an elderly user
 * 
 * @param int $family_member_id Family member's user ID
 * @param int $elderly_id Elderly person's user ID
 * @return bool True if connected, false otherwise
 */
function is_connected($family_member_id, $elderly_id) {
    global $conn;
    $sql = "SELECT * FROM social_connections WHERE user_id = ? AND connection_type = 'family'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $elderly_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Log user activity
 * 
 * @param int $user_id User ID
 * @param string $action Action performed
 * @param string $details Additional details (optional)
 * @return bool True if successful, false otherwise
 */
function log_activity($user_id, $action, $details = '') {
    global $conn;
    $sql = "INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $action, $details);
    return $stmt->execute();
}

/**
 * Generate a random token
 * 
 * @param int $length Length of token
 * @return string Random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get the mood status of an elderly user
 * 
 * @param int $elderly_id Elderly person's user ID
 * @return array|false Mood data or false if not available
 */
function get_mood_status($elderly_id) {
    global $conn;
    $sql = "SELECT * FROM mood_tracking WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $elderly_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

/**
 * Get the class name for mood display based on mood value
 * 
 * @param string $mood Mood value
 * @return string CSS class name
 */
function get_mood_class($mood) {
    switch (strtolower($mood)) {
        case 'happy':
        case 'content':
        case 'excellent':
            return 'success';
        case 'neutral':
        case 'okay':
            return 'warning';
        case 'tired':
        case 'sad':
        case 'stressed':
        case 'unwell':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Check if an emergency alert is active for an elderly user
 * 
 * @param int $elderly_id Elderly person's user ID
 * @return bool True if active alert exists, false otherwise
 */
function has_active_emergency($elderly_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM emergency_alerts 
            WHERE elderly_id = ? AND resolved_at IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $elderly_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

/**
 * Get the rating of a health metric
 * 
 * @param string $metric_name Metric name (e.g., 'systolic', 'heart_rate')
 * @param mixed $value Metric value
 * @return string 'good', 'warning', or 'danger'
 */
function get_health_metric_rating($metric_name, $value) {
    switch ($metric_name) {
        case 'systolic':
            if ($value < 120) return 'good';
            if ($value < 140) return 'warning';
            return 'danger';
        case 'diastolic':
            if ($value < 80) return 'good';
            if ($value < 90) return 'warning';
            return 'danger';
        case 'heart_rate':
            if ($value >= 60 && $value <= 100) return 'good';
            if (($value >= 50 && $value < 60) || ($value > 100 && $value <= 110)) return 'warning';
            return 'danger';
        case 'oxygen':
            if ($value >= 95) return 'good';
            if ($value >= 90) return 'warning';
            return 'danger';
        case 'temperature':
            if ($value >= 36.1 && $value <= 37.2) return 'good';
            if (($value >= 35.5 && $value < 36.1) || ($value > 37.2 && $value <= 38)) return 'warning';
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Format phone number for display
 * 
 * @param string $phone Phone number
 * @return string Formatted phone number
 */
function format_phone($phone) {
    // Generic international format, can be customized for specific country formats
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) > 10) {
        return '+' . substr($phone, 0, strlen($phone) - 10) . ' ' . 
               substr($phone, -10, 3) . '-' . 
               substr($phone, -7, 3) . '-' . 
               substr($phone, -4);
    }
    return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
}

/**
 * Check if a user has permissions to view data for an elderly user
 * 
 * @param int $user_id User ID (family member)
 * @param int $elderly_id Elderly user ID
 * @return bool True if has permission, false otherwise
 */
function has_permission($user_id, $elderly_id) {
    // Admins always have permission
    if ($_SESSION['role'] == 'admin') return true;
    
    // Family members need to be connected
    if ($_SESSION['role'] == 'family_member') {
        return is_connected($user_id, $elderly_id);
    }
    
    // User is viewing their own data
    if ($user_id == $elderly_id) return true;
    
    return false;
}

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    // Check if user_id exists in session and user has a role
    return isset($_SESSION['user_id']) && 
           !empty($_SESSION['user_id']) && 
           isset($_SESSION['user_role']);
}

/**
 * Get all connected elderly relatives
 * @param int $user_id The family member user ID
 * @return array Array of elderly relatives
 */
function get_elderly_relatives($user_id) {
    global $conn;
    $relatives = [];
    
    // Remove u.age, u.gender from the query since they don't exist in your database
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.profile_picture,
               u.address, rc.relationship, rc.status
        FROM users u
        JOIN relative_connections rc ON u.id = rc.elderly_id
        WHERE rc.family_member_id = ? AND rc.status = 'approved'
        ORDER BY u.first_name ASC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $relatives[] = $row;
    }
    
    $stmt->close();
    return $relatives;
}

/**
 * Get pending relative connection requests sent by a family member
 * @param int $user_id The ID of the family member
 * @return array Array of pending connection requests
 */
function get_pending_connection_requests($user_id) {
    global $conn;
    $requests = array();
    
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, 
               rc.id as connection_id, rc.relationship, rc.created_at
        FROM users u
        JOIN relative_connections rc ON u.id = rc.elderly_id
        WHERE rc.family_member_id = ? AND rc.status = 'pending'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    $stmt->close();
    return $requests;
}

/**
 * Get emergency alerts for family member
 * @param int $user_id The family member user ID
 * @return mysqli_result Result set containing emergency alerts
 */
function get_emergency_alerts($user_id) {
    global $conn;
    
    // Get elderly relatives connected to this family member
    $relatives = get_elderly_relatives($user_id);
    $relative_ids = [];
    
    foreach($relatives as $relative) {
        $relative_ids[] = $relative['id'];
    }
    
    // If no relatives, return empty result set
    if (empty($relative_ids)) {
        return mysqli_query($conn, "SELECT * FROM emergency_alerts WHERE 1=0");
    }
    
    // Convert array to comma-separated string for IN clause
    $ids_string = implode(',', $relative_ids);
    
    $query = "SELECT ea.*, u.first_name, u.last_name 
              FROM emergency_alerts ea
              JOIN users u ON ea.elderly_id = u.id
              WHERE ea.elderly_id IN ($ids_string)
              AND ea.status = 'active'
              ORDER BY ea.created_at DESC";
    
    return mysqli_query($conn, $query);
}

/**
 * Get community events 
 * @return mysqli_result Result set containing community events
 */
function get_community_events() {
    global $conn;
    
    $query = "SELECT * FROM community_events 
              WHERE event_date >= CURDATE() 
              ORDER BY event_date ASC, event_time ASC
              LIMIT 10";
    
    return mysqli_query($conn, $query);
}

/**
 * Get scheduled calls for family member
 * @param int $user_id The family member user ID
 * @return mysqli_result Result set containing scheduled calls
 */
function get_scheduled_calls($user_id) {
    global $conn;
    
    // Get elderly relatives connected to this family member
    $relatives = get_elderly_relatives($user_id);
    $relative_ids = [];
    
    foreach($relatives as $relative) {
        $relative_ids[] = $relative['id'];
    }
    
    // If no relatives, return empty result set
    if (empty($relative_ids)) {
        return mysqli_query($conn, "SELECT * FROM scheduled_calls WHERE 1=0");
    }
    
    // Convert array to comma-separated string for IN clause
    $ids_string = implode(',', $relative_ids);
    
    $query = "SELECT sc.*, u.first_name, u.last_name 
              FROM scheduled_calls sc
              JOIN users u ON sc.elderly_id = u.id
              WHERE sc.elderly_id IN ($ids_string)
              AND sc.call_date >= CURDATE()
              ORDER BY sc.call_date ASC, sc.call_time ASC";
    
    return mysqli_query($conn, $query);
}

/**
 * Get life stories associated with family member's elderly relatives
 * @param int $user_id The family member user ID
 * @return mysqli_result Result set containing life stories
 */
function get_life_stories($user_id) {
    global $conn;
    
    // Get elderly relatives connected to this family member
    $relatives = get_elderly_relatives($user_id);
    $relative_ids = [];
    
    foreach($relatives as $relative) {
        $relative_ids[] = $relative['id'];
    }
    
    // If no relatives, return empty result set
    if (empty($relative_ids)) {
        return mysqli_query($conn, "SELECT * FROM life_stories WHERE 1=0");
    }
    
    // Convert array to comma-separated string for IN clause
    $ids_string = implode(',', $relative_ids);
    
    $query = "SELECT ls.*, u.first_name, u.last_name
              FROM life_stories ls
              JOIN users u ON ls.elderly_id = u.id
              WHERE ls.elderly_id IN ($ids_string)
              ORDER BY ls.created_at DESC
              LIMIT 5";
    
    return mysqli_query($conn, $query);
}

/**
 * Get health metrics for a specific elderly relative
 * @param int $relative_id The elderly user's ID
 * @return array Array of health metrics data
 */
function get_relative_health_metrics($relative_id) {
    global $conn;
    $metrics = array();
    
    // First verify that the logged-in user has access to this elderly person
    $user_id = $_SESSION['user_id'];
    $access_check = $conn->prepare("
        SELECT id FROM relative_connections 
        WHERE family_member_id = ? AND elderly_id = ? AND status = 'approved'
    ");
    $access_check->bind_param("ii", $user_id, $relative_id);
    $access_check->execute();
    $result = $access_check->get_result();
    
    if ($result->num_rows === 0) {
        // No access
        return array();
    }
    
    // If access is granted, fetch health data
    $stmt = $conn->prepare("
        SELECT * FROM health_metrics 
        WHERE user_id = ? 
        ORDER BY recorded_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $relative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $metrics[] = $row;
    }
    
    $stmt->close();
    return $metrics;
}

/**
 * Get medication data for a specific elderly relative
 * @param int $relative_id The elderly user's ID
 * @return array Array of medication data
 */
function get_relative_medications($relative_id) {
    global $conn;
    $medications = array();
    
    // First verify that the logged-in user has access to this elderly person
    $user_id = $_SESSION['user_id'];
    $access_check = $conn->prepare("
        SELECT id FROM relative_connections 
        WHERE family_member_id = ? AND elderly_id = ? AND status = 'approved'
    ");
    $access_check->bind_param("ii", $user_id, $relative_id);
    $access_check->execute();
    $result = $access_check->get_result();
    
    if ($result->num_rows === 0) {
        // No access
        return array();
    }
    
    // If access is granted, fetch medication data
    $stmt = $conn->prepare("
        SELECT m.*, 
               CASE 
                   WHEN ml.status = 'taken' THEN 'taken'
                   WHEN ml.status = 'skipped' THEN 'skipped'
                   WHEN ml.status = 'missed' THEN 'missed'
                   WHEN TIME(NOW()) > TIME(ml.scheduled_time) AND ml.status = 'pending' THEN 'overdue'
                   ELSE 'pending'
               END as current_status,
               ml.scheduled_time
        FROM medications m
        LEFT JOIN medication_logs ml ON m.id = ml.medication_id AND DATE(ml.scheduled_time) = CURDATE()
        WHERE m.user_id = ? AND m.is_active = 1
        ORDER BY ml.scheduled_time ASC
    ");
    $stmt->bind_param("i", $relative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
    
    $stmt->close();
    return $medications;
}
?>