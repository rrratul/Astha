<?php

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

/**
 * Redirect to appropriate dashboard based on user role
 * Includes redirect loop prevention
 */
function redirectToDashboard($role) {
    // Get current page information to prevent redirect loops
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_uri = $_SERVER['REQUEST_URI'];
    
    // Prevent redirect loops by checking if we're already on the correct page
    switch ($role) {
        case 'senior':
            // Only redirect if we're not already on the elderly dashboard
            if (strpos($current_uri, '/elderly/') === false || $current_page === 'login_user.php') {
                header("Location: ../elderly/index.php");
                exit();
            }
            break;
            
        case 'caregiver':
            // Only redirect if we're not already on the caregiver dashboard
            if (strpos($current_uri, '/caregiver/') === false || $current_page === 'login_user.php') {
                header("Location: ../caregiver/index.php");
                exit();
            }
            break;
            
        case 'family_member':
            // Only redirect if we're not already on the family dashboard
            if (strpos($current_uri, '/family/') === false || $current_page === 'login_user.php') {
                header("Location: ../family/index.php");
                exit();
            }
            break;
            
        case 'healthcare_provider':
            // Only redirect if we're not already on the healthcare dashboard
            if (strpos($current_uri, '/healthcare/') === false || $current_page === 'login_user.php') {
                header("Location: ../healthcare/index.php");
                exit();
            }
            break;
            
        default:
            // Default to elderly dashboard for unknown roles
            if (strpos($current_uri, '/elderly/') === false || $current_page === 'login_user.php') {
                header("Location: ../elderly/index.php");
                exit();
            }
            break;
    }
}

/**
 * Check authentication and redirect if not logged in
 */
function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: ../login_user/login_user.php");
        exit();
    }
}

/**
 * Check if user has the correct role for the current page
 */
function checkUserRole($requiredRole) {
    // First check if user is logged in
    if (!isLoggedIn()) {
        header("Location: ../login_user/login_user.php");
        exit();
    }
    
    // Check if user has the required role
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
        // Redirect to their correct dashboard
        if (isset($_SESSION['user_role'])) {
            redirectToDashboard($_SESSION['user_role']);
        } else {
            header("Location: ../login_user/login_user.php");
        }
        exit();
    }
}

/**
 * Generate secure token for remember me functionality
 */
function generateSecureToken($length = 32) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length / 2));
    } else {
        // Fallback for older PHP versions
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }
}

/**
 * Log user activity
 */
function logUserActivity($user_id, $action, $description = '') {
    $log_entry = date('Y-m-d H:i:s') . " - User ID: $user_id - Action: $action - Description: $description" . PHP_EOL;
    $log_file = __DIR__ . '/activity_log.txt';
    
    // Create log file if it doesn't exist
    if (!file_exists($log_file)) {
        touch($log_file);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
}

/**
 * Safe redirect function
 */
function safeRedirect($url) {
    // Prevent header injection
    $url = filter_var($url, FILTER_SANITIZE_URL);
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        // Fallback to JavaScript redirect if headers already sent
        echo "<script>window.location.href = '" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "';</script>";
        exit();
    }
}

/**
 * Get user role display name
 */
function getRoleDisplayName($role) {
    switch ($role) {
        case 'senior':
            return 'Senior Citizen';
        case 'caregiver':
            return 'Caregiver';
        case 'family_member':
            return 'Family Member';
        case 'healthcare_provider':
            return 'Healthcare Provider';
        default:
            return 'User';
    }
}

/**
 * Clean and validate session data
 */
function validateSession() {
    // Check if session is valid and not expired
    if (isset($_SESSION['login_time'])) {
        $session_lifetime = 24 * 60 * 60; // 24 hours
        if (time() - $_SESSION['login_time'] > $session_lifetime) {
            // Session expired
            session_destroy();
            return false;
        }
    }
    
    // Validate required session variables
    $required_vars = ['user_id', 'user_email', 'user_role', 'logged_in'];
    foreach ($required_vars as $var) {
        if (!isset($_SESSION[$var])) {
            return false;
        }
    }
    
    return true;
}

/**
 * Secure logout function
 */
function secureLogout() {
    // Clear remember me cookie if it exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if database connection is available
 */
function checkDatabaseConnection() {
    global $conn;
    if (!$conn || $conn->connect_error) {
        error_log("Database connection failed: " . ($conn ? $conn->connect_error : "Connection object not found"));
        return false;
    }
    return true;
}

?>