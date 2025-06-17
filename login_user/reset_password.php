<?php
// login_user/reset_password.php
session_start();
require_once '../signup_user/config.php';

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_data = null;

// Validate token
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT id, first_name, email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $valid_token = true;
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL, login_attempts = 0 WHERE id = ?");
        $updateStmt->bind_param("si", $hashed_password, $user_data['id']);
        
        if ($updateStmt->execute()) {
            $success = "Your password has been successfully reset. You can now login with your new password.";
            
            // Log password reset
            file_put_contents(__DIR__ . '/password_reset_log.txt', date('Y-m-d H:i:s') . " - Password reset completed for: " . $user_data['email'] . "\n", FILE_APPEND);
            
            // Set session message for login page
            $_SESSION['reset_success'] = "Password reset successful. Please login with your new password.";
            
            // Redirect to login page after 3 seconds
            header("refresh:3;url=./login_user.php");
        } else {
            $errors[] = "An error occurred while resetting your password. Please try again.";
        }
        $updateStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Shohochor</title>
    <link rel="stylesheet" href="./login.css">
</head>
<body>
    <?php require_once '../includes/nav.php'; ?>
    
    <div class="login-container">
        <div class="form-heading">
            <h1>Set New Password</h1>
            <?php if ($valid_token): ?>
                <p>Enter your new password for <?php echo htmlspecialchars($user_data['email']); ?></p>
            <?php else: ?>
                <p>Invalid or expired reset link</p>
            <?php endif; ?>
        </div>
        
        <div class="login-form">
            <?php if (!$valid_token): ?>
                <div class="error-message">
                    <p>This password reset link is invalid or has expired.</p>
                    <p>Please <a href="./forgot_password.php">request a new password reset</a>.</p>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                    <p>Redirecting to login page...</p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . urlencode($token); ?>">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               minlength="8" placeholder="Enter your new password">
                        <small>Password must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Confirm your new password">
                    </div>
                    
                    <button type="submit" class="submit-btn">Reset Password</button>
                    
                    <div class="signup-link">
                        <p><a href="./login_user.php">Back to Login</a></p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>