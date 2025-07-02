<?php


function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

function redirectToDashboard($role) {
    // Get current script path to prevent loops
    $current_script = $_SERVER['SCRIPT_NAME'];
    
    switch ($role) {
        case 'senior':
        case 'elderly': // Handle both role names
            if (strpos($current_script, '/elderly/index.php') === false) {
                header("Location: ../elderly/index.php");
                exit();
            }
            break;
        case 'caregiver':
            if (strpos($current_script, '/caregiver/index.php') === false) {
                header("Location: ../caregiver/index.php");
                exit();
            }
            break;
        case 'family_member':
            if (strpos($current_script, '/relative/index.php') === false) {
                header("Location: ../relative/index.php");
                exit();
            }
            break;
        case 'healthcare_provider':
            if (strpos($current_script, '/healthcare/index.php') === false) {
                header("Location: ../healthcare/index.php");
                exit();
            }
            break;
        default:
            if (strpos($current_script, '/elderly/index.php') === false) {
                header("Location: ../elderly/index.php");
                exit();
            }
            break;
    }
}

function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: ../login_user/login_user.php");
        exit();
    }
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

?>