<?php

session_start();
require_once './config.php';

// Check if user has pending verification
if (!isset($_SESSION['pending_user'])) {
    header("Location: ./signup_user.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');
    $pending_user = $_SESSION['pending_user'];
    
    if (empty($entered_otp)) {
        $error = "Please enter the verification code";
    } elseif ($entered_otp !== $pending_user['otp']) {
        $error = "Invalid verification code";
    } elseif (strtotime($pending_user['otp_expiry']) < time()) {
        $error = "Verification code has expired";
    } else {
        // OTP is valid, create the user
        try {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, date_of_birth, role, email_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
            
            $stmt->bind_param("ssssssss", 
                $pending_user['first_name'],
                $pending_user['last_name'],
                $pending_user['email'],
                $pending_user['phone'],
                $pending_user['password'],
                $pending_user['dob'],
                $pending_user['role'],
                $pending_user['verification_token']
            );
            
            if ($stmt->execute()) {
                // Clear pending user data
                unset($_SESSION['pending_user']);
                
                $success = "Account created successfully! You can now login.";
                
                // Redirect to login page after 3 seconds
                header("refresh:3;url=../login_user/login_user.php");
            } else {
                $error = "Error creating account: " . $stmt->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Shohochor</title>
    <link rel="stylesheet" href="./sigup.css">
</head>
<body>
    <div class="signup-container">
        <div class="form-heading">
            <h1>Verify Your Email</h1>
            <p>We've sent a verification code to <?php echo htmlspecialchars($_SESSION['verification_email'] ?? ''); ?></p>
        </div>
        
        <div class="signup-form">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="post">
                    <div class="form-group">
                        <label for="otp">Verification Code</label>
                        <input type="text" id="otp" name="otp" class="form-control" 
                               required maxlength="6" pattern="[0-9]{6}" 
                               placeholder="Enter 6-digit code">
                    </div>
                    
                    <button type="submit" class="submit-btn">Verify Email</button>
                </form>
                
                <div class="login-link" style="margin-top: 20px;">
                    <p><a href="./signup_user.php">Back to Sign Up</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>