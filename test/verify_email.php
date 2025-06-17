<?php
// Make sure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../signup_user/functions.php';
require_once '../PHPMailer-master/src/Exception.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error = '';
$success = '';
$email = '';

// Check if we have an email to verify in the session
if (isset($_SESSION['verification_email'])) {
    $email = $_SESSION['verification_email'];
} else {
    // If no email in session, check if it's in the query string (for when clicking verification link from email)
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    
    // If still no email, redirect to login
    if (empty($email)) {
        header("Location: ../login_user/login_user.php");
        exit();
    }
    
    // Store it in session for future use
    $_SESSION['verification_email'] = $email;
}

/**
 * Send verification email with OTP using PHPMailer
 */
function sendVerificationEmail($email, $name, $otp) {
    try {
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->SMTPDebug = 0; // Set to 0 for production
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shohochor.ai@gmail.com'; 
        $mail->Password = 'vuet esfo cjob euee'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Sender and recipient
        $mail->setFrom('shohochor.ai@gmail.com', 'Shohochor');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('shohochor.ai@gmail.com', 'Shohochor Support');
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Shohochor Account";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2>Welcome to Shohochor!</h2>
            <p>Hello $name,</p>
            <p>Thank you for registering with Shohochor. To complete your registration, please use the following verification code:</p>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px;'>
                $otp
            </div>
            <p>This code will expire in 1 hour.</p>
            <p>If you did not request this code, please ignore this email.</p>
            <p>Best regards,<br>The Shohochor Team</p>
        </div>";
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Hello $name, Your verification code is: $otp. This code will expire in 1 hour.";
        
        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a random OTP of specified length
 */
function generateOTP($length = 6) {
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, 9)];
    }
    return $otp;
}

// Handle OTP verification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    // Get the OTP from the form
    $enteredOTP = trim($_POST['otp']);
    
    // Validate OTP input
    if (empty($enteredOTP)) {
        $error = "Please enter the verification code.";
    } else {
        try {
            // Prepare statement to check OTP
            $stmt = $conn->prepare("SELECT id, first_name, reset_token, reset_token_expiry FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = "Account not found. Please sign up.";
            } else {
                $user = $result->fetch_assoc();
                $storedOTP = $user['reset_token'];
                $otpExpiry = strtotime($user['reset_token_expiry']);
                $currentTime = time();
                
                // Check if OTP is valid and not expired
                if ($storedOTP === $enteredOTP && $currentTime <= $otpExpiry) {
                    // Update user status and mark email as verified
                    $updateStmt = $conn->prepare("UPDATE users SET status = 'active', email_verified = 1, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
                    $updateStmt->bind_param("s", $email);
                    
                    if ($updateStmt->execute()) {
                        $success = "Email verified successfully! You can now log in.";
                        
                        // Set success message in session for login page
                        $_SESSION['success_message'] = "Email verified successfully! You can now log in.";
                        
                        // Clear verification email from session
                        unset($_SESSION['verification_email']);
                        
                        // Redirect to login after 3 seconds
                        header("Refresh: 3; URL=../login_user/login_user.php");
                    } else {
                        $error = "Failed to verify email. Please try again.";
                    }
                    
                    $updateStmt->close();
                } else {
                    if ($currentTime > $otpExpiry) {
                        $error = "Verification code has expired. Please request a new one.";
                    } else {
                        $error = "Invalid verification code. Please try again.";
                    }
                }
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            error_log("Error in email verification: " . $e->getMessage());
        }
    }
}

// Handle resend OTP request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
    try {
        // Get user details
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Account not found. Please sign up.";
        } else {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $name = $user['first_name'];
            
            // Generate new OTP and expiry
            $newOTP = generateOTP(6);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with new OTP
            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $newOTP, $otp_expiry, $user_id);
            
            if ($updateStmt->execute()) {
                // Send verification email with new OTP
                $emailSent = sendVerificationEmail($email, $name, $newOTP);
                
                if ($emailSent) {
                    $success = "A new verification code has been sent to your email.";
                } else {
                    $error = "Failed to send verification email. Please try again.";
                }
            } else {
                $error = "Failed to generate new verification code. Please try again.";
            }
            
            $updateStmt->close();
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
        error_log("Error in resend OTP: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en" ⚡>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Verify Your Email - Shohochor</title>
    <meta name="description" content="Verify your email address to complete your Shohochor account registration.">
    
    <!-- AMP required scripts -->
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
    
    <!-- Critical AMP Boilerplate -->
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
    <noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    
    <style amp-custom>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        p {
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            letter-spacing: 4px;
            text-align: center;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .resend-btn {
            background-color: transparent;
            border: 1px solid #3498db;
            color: #3498db;
            margin-top: 15px;
        }
        
        .resend-btn:hover {
            background-color: #f5f7fa;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            font-weight: 500;
            text-align: left;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            font-weight: 500;
            text-align: left;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .digit-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Email</h1>
        <p>We've sent a verification code to: <strong><?php echo htmlspecialchars($email); ?></strong></p>
        
        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Display success message if any -->
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- OTP Verification Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="otp">Enter Verification Code</label>
                <input type="text" id="otp" name="otp" placeholder="Enter 6-digit code" maxlength="6" autocomplete="off" required>
            </div>
            
            <button type="submit" name="verify_otp">Verify Email</button>
        </form>
        
        <!-- Resend OTP Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <button type="submit" name="resend_otp" class="resend-btn">Resend Verification Code</button>
        </form>
        
        <div class="login-link">
            <p>Already verified? <a href="../login_user/login_user.php">Log In</a></p>
        </div>
    </div>
</body>
</html>