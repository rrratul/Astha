<?php

// Initialize the session
session_start();

// Unset all session variables
$_SESSION = array();

// Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set a logout message in a temporary cookie
setcookie("logout_message", "You have been successfully logged out.", time() + 60, "/");

// Redirect to login page
header("Location: ../../login_user/login_user.php");
exit();
?>