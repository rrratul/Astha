<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
session_destroy();

// Use JavaScript redirect as fallback
echo '<script type="text/javascript">';
echo 'window.location.href="/Astha/index.php";';
echo '</script>';
echo '<noscript>';
echo '<meta http-equiv="refresh" content="0;url=/Astha/index.php" />';
echo '</noscript>';
exit();
?>