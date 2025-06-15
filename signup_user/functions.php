<?php
/**
 * Functions file with helper functions for the Shohochor platform
 */

/**
 * Check if user is logged in
 * @return bool Whether user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has specified role
 * @param string|array $roles Role(s) to check against
 * @return bool Whether user has the specified role
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['user_role'], $roles);
    }
    
    return $_SESSION['user_role'] === $roles;
}

/**
 * Redirect user if not logged in
 * @param string $redirect_url URL to redirect to if not logged in
 */
function requireLogin($redirect_url = '/login_user/login_user.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Redirect user if not authorized (doesn't have required role)
 * @param string|array $roles Role(s) required to access page
 * @param string $redirect_url URL to redirect to if not authorized
 */
function requireRole($roles, $redirect_url = '/login_user/login_user.php') {
    requireLogin($redirect_url);
    
    if (!hasRole($roles)) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Get formatted date
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date string
 */
function formatDate($date, $format = 'M j, Y') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Format date and time
 * @param string $datetime Date and time string
 * @param string $format Format string
 * @return string Formatted date and time string
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    $dateObj = new DateTime($datetime);
    return $dateObj->format($format);
}

/**
 * Truncate text to specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append to truncated text
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . $append;
    }
    return $text;
}

/**
 * Sanitize input data
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Get dashboard URL based on user role
 * @param string $role User role
 * @return string Dashboard URL
 */
function getDashboardUrl($role = null) {
    if ($role === null && isset($_SESSION['user_role'])) {
        $role = $_SESSION['user_role'];
    }
    
    switch ($role) {
        case 'admin':
            return '/admin/dashboard.php';
        case 'elderly':
            return '/dashboards/senior_dashboard.php';
        case 'caregiver':
            return '/dashboards/caregiver_dashboard.php';
        case 'family_member':
            return '/dashboards/family_dashboard.php';
        case 'healthcare_provider':
            return '/dashboards/healthcare_dashboard.php';
        default:
            return '/index.php';
    }
}

/**
 * Generate a random string
 * @param int $length Length of random string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Display error message
 * @param string $message Error message
 * @return string HTML for error message
 */
function displayError($message) {
    return '<div class="error-message">' . $message . '</div>';
}

/**
 * Display success message
 * @param string $message Success message
 * @return string HTML for success message
 */
function displaySuccess($message) {
    return '<div class="success-message">' . $message . '</div>';
}

/**
 * Check if email exists in database
 * @param string $email Email to check
 * @param PDO $conn Database connection
 * @return bool Whether email exists
 */
function emailExists($email, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}
?>