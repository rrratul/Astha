<?php

session_start();
require_once '../signup_user/config.php';
require_once '../signup_user/verification.php';

$errors = [];
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, first_name, email FROM users WHERE email = ? AND email_verified = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token in database
            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ?, login_attempts = 0 WHERE id = ?");
            $updateStmt->bind_param("ssi", $reset_token, $reset_expiry, $user['id']);
            
            if ($updateStmt->execute()) {
                // Send reset email
                if (sendPasswordResetEmail($user['email'], $user['first_name'], $reset_token)) {
                    $success = "Password reset instructions have been sent to your email address.";
                    
                    // Log password reset request
                    file_put_contents(__DIR__ . '/password_reset_log.txt', date('Y-m-d H:i:s') . " - Password reset requested for: " . $email . "\n", FILE_APPEND);
                } else {
                    $errors[] = "Failed to send password reset email. Please try again.";
                }
            } else {
                $errors[] = "An error occurred. Please try again.";
            }
            $updateStmt->close();
        } else {
            // Don't reveal if email exists or not for security
            $success = "If an account with that email exists, password reset instructions have been sent.";
        }
        $stmt->close();
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $name, $reset_token) {
    try {
        require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
        require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

        use PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\Exception;  
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shohochor.ai@gmail.com';
        $mail->Password = 'fahh fgzi bmfp slbs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('shohochor.ai@gmail.com', 'Shohochor');
        $mail->addAddress($email, $name);
        
        // Reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Astha/login_user/reset_password.php?token=" . $reset_token;
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request - Shohochor";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; padding: 20px;'>
                <h1 style='color: #0096ad;'>Shohochor</h1>
            </div>
            <div style='background-color: #f5f7f9; padding: 20px; border-radius: 5px;'>
                <h2>Hello $name,</h2>
                <p>We received a request to reset your password for your Shohochor account.</p>
                <p>Click the button below to reset your password:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$reset_link' style='background-color: #0096ad; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </div>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all;'><a href='$reset_link'>$reset_link</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
            </div>
            <div style='text-align: center; margin-top: 20px; font-size: 12px; color: #666;'>
                <p>&copy; " . date('Y') . " Shohochor. All rights reserved.</p>
            </div>
        </div>";
        
        $mail->AltBody = "Hello $name, Click this link to reset your password: $reset_link (expires in 1 hour)";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Password reset email error: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Shohochor</title>
    <link rel="stylesheet" href="./login.css">
</head>
<body>
    <?php require_once '../includes/nav.php'; ?>
    
    <div class="login-container">
        <div class="form-heading">
            <h1>Reset Your Password</h1>
            <p>Enter your email address and we'll send you instructions to reset your password</p>
        </div>
        
        <div class="login-form">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address">
                </div>
                
                <button type="submit" class="submit-btn">Send Reset Instructions</button>
                
                <div class="signup-link">
                    <p><a href="./login_user.php">Back to Login</a></p>
                    <p>Don't have an account? <a href="../signup_user/signup_user.php">Sign up here</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>