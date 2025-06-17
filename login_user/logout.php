<?php

session_start();
require_once '../signup_user/config.php';

// Log logout
if (isset($_SESSION['user_email'])) {
    file_put_contents(__DIR__ . '/login_log.txt', date('Y-m-d H:i:s') . " - User logged out: " . $_SESSION['user_email'] . "\n", FILE_APPEND);
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Clear from database
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: ./login_user.php");
exit();
?>