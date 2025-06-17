<?php

session_start();
require_once './config.php';
require_once './functions.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For debugging - check if the users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    die("The 'users' table doesn't exist in your database");
}

// Initialize variables
$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
    'dob' => '',
    'user_type' => 'senior',
    'terms' => ''
];

/**
 * Generate a random OTP of specified length
 */
function generateOTP($length = 6) {
    return sprintf("%0{$length}d", mt_rand(0, pow(10, $length) - 1));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add debug output
    // echo "<div style='background: yellow; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    // echo "<h3>DEBUG: Form submission detected</h3>";
    // echo "POST data received: <pre>" . print_r($_POST, true) . "</pre>";
    // echo "</div>";
    
    // Collect and sanitize form data
    $formData['first_name'] = trim($_POST['first_name'] ?? '');
    $formData['last_name'] = trim($_POST['last_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['password'] = $_POST['password'] ?? '';
    $formData['confirm_password'] = $_POST['confirm_password'] ?? '';
    $formData['dob'] = trim($_POST['dob'] ?? '');
    $formData['user_type'] = $_POST['user_type'] ?? 'senior';
    $formData['terms'] = isset($_POST['terms']) ? 1 : 0;
    
    echo "<div style='background: lightblue; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>DEBUG: Form data collected</h3>";
    echo "Email: " . $formData['email'] . "<br>";
    echo "Name: " . $formData['first_name'] . " " . $formData['last_name'] . "<br>";
    echo "User Type: " . $formData['user_type'] . "<br>";
    echo "</div>";
    
    // Map user type to role
    $roleMap = [
        'senior' => 'elderly',
        'caregiver' => 'caregiver',
        'family' => 'family_member',
        'healthcare_provider' => 'healthcare_provider'
    ];
    $role = $roleMap[$formData['user_type']] ?? 'elderly';
    
    // Validate form data
    if (empty($formData['first_name'])) {
        $errors[] = "First name is required";
    }
    if (empty($formData['last_name'])) {
        $errors[] = "Last name is required";
    }
    if (empty($formData['email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    if (empty($formData['password'])) {
        $errors[] = "Password is required";
    } elseif (strlen($formData['password']) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if ($formData['password'] !== $formData['confirm_password']) {
        $errors[] = "Passwords do not match";
    }
    if (empty($formData['terms'])) {
        $errors[] = "You must agree to the Terms and Conditions";
    }
    
    echo "<div style='background: orange; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<h3>DEBUG: Validation results</h3>";
    if (empty($errors)) {
        echo "Validation passed - no errors found";
    } else {
        echo "Validation errors: <pre>" . print_r($errors, true) . "</pre>";
    }
    echo "</div>";
    
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email or login.";
            echo "<div style='background: red; color: white; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
            echo "<h3>DEBUG: Email already exists</h3>";
            echo "</div>";
        } else {
            echo "<div style='background: lightgreen; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
            echo "<h3>DEBUG: Email is available, proceeding with email sending</h3>";
            echo "</div>";
            
            try {
                // Generate OTP and verification token
                $otp = generateOTP(6);
                $otp_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $verification_token = bin2hex(random_bytes(16));
                
                echo "<div style='background: cyan; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
                echo "<h3>DEBUG: Generated OTP and token</h3>";
                echo "OTP: $otp<br>";
                echo "Expiry: $otp_expiry<br>";
                echo "</div>";
                
                // Store user data and OTP in session for verification
                $_SESSION['pending_user'] = [
                    'first_name' => $formData['first_name'],
                    'last_name' => $formData['last_name'],
                    'email' => $formData['email'],
                    'phone' => $formData['phone'],
                    'password' => password_hash($formData['password'], PASSWORD_DEFAULT),
                    'dob' => $formData['dob'],
                    'role' => $role,
                    'otp' => $otp,
                    'otp_expiry' => $otp_expiry,
                    'verification_token' => $verification_token
                ];
                
                // Log the attempt
                file_put_contents(__DIR__ . '/signup_log.txt', date('Y-m-d H:i:s') . " - Attempting to send OTP to: " . $formData['email'] . " | OTP: $otp\n", FILE_APPEND);
                
                // Include verification functions
                require_once './verification.php';
                
                echo "<div style='background: purple; color: white; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
                echo "<h3>DEBUG: About to call sendVerificationEmail function</h3>";
                echo "</div>";
                
                // Send verification email with OTP
                $emailSent = sendVerificationEmail($formData['email'], $formData['first_name'], $otp);
                
                if ($emailSent) {
                    echo "<div style='background: green; color: white; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
                    echo "<h3>DEBUG: Email sent successfully!</h3>";
                    echo "</div>";
                    
                    // Store email in session for verification page
                    $_SESSION['verification_email'] = $formData['email'];
                    $_SESSION['success_message'] = "Please check your email for the verification code.";
                    
                    // Log success
                    file_put_contents(__DIR__ . '/signup_log.txt', date('Y-m-d H:i:s') . " - OTP sent successfully to: " . $formData['email'] . "\n", FILE_APPEND);
                    
                    echo "<p style='background: green; color: white; padding: 20px; text-align: center;'>
                        <strong>Success!</strong> Verification email sent. Redirecting to verification page in 3 seconds...
                    </p>";
                    
                    // Use JavaScript redirect for debugging
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = './verify_email.php';
                        }, 3000);
                    </script>";
                    
                } else {
                    echo "<div style='background: red; color: white; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
                    echo "<h3>DEBUG: Email sending failed!</h3>";
                    echo "</div>";
                    
                    $errors[] = "We couldn't send the verification email. Please try again.";
                    
                    // Clear session data on email failure
                    unset($_SESSION['pending_user']);
                    
                    // Log the error for debugging
                    error_log("Failed to send verification email to: " . $formData['email']);
                    file_put_contents(__DIR__ . '/signup_error.log', date('Y-m-d H:i:s') . " - Failed to send email to: " . $formData['email'] . "\n", FILE_APPEND);
                }
                
            } catch (Exception $e) {
                echo "<div style='background: red; color: white; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
                echo "<h3>DEBUG: Exception occurred</h3>";
                echo "Error: " . $e->getMessage();
                echo "</div>";
                
                $errors[] = "An error occurred: " . $e->getMessage();
                error_log("Exception in signup: " . $e->getMessage());
                file_put_contents(__DIR__ . '/signup_error.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Shohochor</title>
    <link rel="stylesheet" href="./sigup.css">
</head>
<body>
    <?php
    // Include header
    require_once '../includes/nav.php';
    ?>
    <div class="signup-container">
        <div class="form-heading">
            <h1>Create Your Shohochor Account</h1>
            <p>Join our platform to access innovative elderly care solutions</p>
        </div>
        
        <div class="signup-form">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($formData['first_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo htmlspecialchars($formData['email']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['dob']); ?>">
                </div>
                
                <div class="form-group">
                    <label>I am a: *</label>
                    <div class="user-type-selector">
                        <label class="user-type-option <?php echo ($formData['user_type'] === 'senior') ? 'selected' : ''; ?>">
                            <input type="radio" class="user-type-radio" name="user_type" value="senior" 
                                   <?php echo ($formData['user_type'] === 'senior') ? 'checked' : ''; ?> required>
                            <h3>Senior</h3>
                        </label>
                        
                        <label class="user-type-option <?php echo ($formData['user_type'] === 'caregiver') ? 'selected' : ''; ?>">
                            <input type="radio" class="user-type-radio" name="user_type" value="caregiver" 
                                   <?php echo ($formData['user_type'] === 'caregiver') ? 'checked' : ''; ?>>
                            <h3>Caregiver</h3>
                        </label>
                        
                        <label class="user-type-option <?php echo ($formData['user_type'] === 'family') ? 'selected' : ''; ?>">
                            <input type="radio" class="user-type-radio" name="user_type" value="family" 
                                   <?php echo ($formData['user_type'] === 'family') ? 'checked' : ''; ?>>
                            <h3>Family Member</h3>
                        </label>
                        
                        <label class="user-type-option <?php echo ($formData['user_type'] === 'healthcare_provider') ? 'selected' : ''; ?>">
                            <input type="radio" class="user-type-radio" name="user_type" value="healthcare_provider" 
                                   <?php echo ($formData['user_type'] === 'healthcare_provider') ? 'checked' : ''; ?>>
                            <h3>Healthcare Provider</h3>
                        </label>
                    </div>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="terms" name="terms" required 
                           <?php echo $formData['terms'] ? 'checked' : ''; ?>>
                    <label for="terms">I agree to the Terms of Service and Privacy Policy *</label>
                </div>
                
                <button type="submit" class="submit-btn">Create Account</button>
                
                <div class="login-link">
                    <p>Already have an account? <a href="../login_user/login_user.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
     <?php
    // Include header
    require_once '../includes/footer.php';
    ?>

    <script>
        // Handle user type selection
        document.querySelectorAll('.user-type-option').forEach(function(option) {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.user-type-option').forEach(function(opt) {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option and check its radio
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html>