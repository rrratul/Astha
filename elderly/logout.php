
<?php
session_start();

// Destroy session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Clear any remember me cookies
setcookie('remember_token', '', time() - 3600, '/');

// Redirect to login
header("Location: ../login_user/login_user.php");
exit();
?>