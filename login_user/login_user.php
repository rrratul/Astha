<?php

session_start();
require_once '../signup_user/config.php';
require_once './functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
}

// Initialize variables
$errors = [];
$email = '';
$remember_me = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        // Check if user exists and get user data
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, email_verified, status, login_attempts FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is verified
            if (!$user['email_verified']) {
                $errors[] = "Please verify your email address before logging in.";
            }
            // Check if account is active (handle missing status column)
            elseif (isset($user['status']) && $user['status'] !== 'active') {
                $errors[] = "Your account has been suspended. Please contact support.";
            }
            // Check if account is locked due to too many failed attempts
            elseif ($user['login_attempts'] >= 5) {
                $errors[] = "Account locked due to too many failed login attempts. Please reset your password.";
            }
            // Verify password
            elseif (password_verify($password, $user['password'])) {
                // Successful login
                
                // Check if last_activity column exists before updating
                $columns = [];
                $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
                $hasLastActivity = $columnCheck->num_rows > 0;
                
                if ($hasLastActivity) {
                    $updateStmt = $conn->prepare("UPDATE users SET login_attempts = 0, last_login = NOW(), last_activity = NOW() WHERE id = ?");
                } else {
                    $updateStmt = $conn->prepare("UPDATE users SET login_attempts = 0, last_login = NOW() WHERE id = ?");
                }
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Handle remember me
                if ($remember_me) {
                    $remember_token = generateSecureToken(32);
                    $remember_expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Check if remember_token_expiry column exists
                    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token_expiry'");
                    $hasTokenExpiry = $columnCheck->num_rows > 0;
                    
                    if ($hasTokenExpiry) {
                        $tokenStmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = FROM_UNIXTIME(?) WHERE id = ?");
                        $tokenStmt->bind_param("sii", $remember_token, $remember_expiry, $user['id']);
                    } else {
                        $tokenStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $tokenStmt->bind_param("si", $remember_token, $user['id']);
                    }
                    $tokenStmt->execute();
                    
                    // Set cookie
                    setcookie('remember_token', $remember_token, $remember_expiry, '/', '', false, true);
                }
                
                // Log successful login (check if function exists)
                if (function_exists('logUserActivity')) {
                    logUserActivity($user['id'], 'login', 'Successful login');
                }
                file_put_contents(__DIR__ . '/login_log.txt', date('Y-m-d H:i:s') . " - Successful login: " . $email . " (Role: " . $user['role'] . ")\n", FILE_APPEND);
                
                // Redirect to appropriate dashboard based on role
                redirectToDashboard($user['role']);
                
            } else {
                // Invalid password - increment login attempts
                $newAttempts = $user['login_attempts'] + 1;
                $updateStmt = $conn->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $newAttempts, $user['id']);
                $updateStmt->execute();
                
                $errors[] = "Invalid email or password. Attempt " . $newAttempts . "/5";
                
                // Log failed login attempt
                if (function_exists('logUserActivity')) {
                    logUserActivity($user['id'], 'failed_login', "Failed login attempt $newAttempts/5");
                }
                file_put_contents(__DIR__ . '/login_log.txt', date('Y-m-d H:i:s') . " - Failed login attempt: " . $email . " (Attempt: $newAttempts)\n", FILE_APPEND);
            }
        } else {
            $errors[] = "Invalid email or password";
            
            // Log failed login attempt for non-existent user
            file_put_contents(__DIR__ . '/login_log.txt', date('Y-m-d H:i:s') . " - Failed login attempt: " . $email . " (User not found)\n", FILE_APPEND);
        }
        $stmt->close();
    }
}

// Check for remember me cookie on page load
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $remember_token = $_COOKIE['remember_token'];
    
    // Check if remember_token_expiry column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token_expiry'");
    $hasTokenExpiry = $columnCheck->num_rows > 0;
    
    if ($hasTokenExpiry) {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE remember_token = ? AND remember_token_expiry > NOW() AND (status = 'active' OR status IS NULL)");
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE remember_token = ? AND (status = 'active' OR status IS NULL)");
    }
    $stmt->bind_param("s", $remember_token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Auto login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Update last login and activity (if columns exist)
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
        $hasLastActivity = $columnCheck->num_rows > 0;
        
        if ($hasLastActivity) {
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW(), last_activity = NOW() WHERE id = ?");
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        }
        $updateStmt->bind_param("i", $user['id']);
        $updateStmt->execute();
        
        // Log auto login
        if (function_exists('logUserActivity')) {
            logUserActivity($user['id'], 'auto_login', 'Auto login via remember me token');
        }
        
        // Redirect to appropriate dashboard
        redirectToDashboard($user['role']);
    } else {
        // Invalid or expired token, clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Shohochor</title>
    <link rel="stylesheet" href="./login.css">
    <style>
        .role-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #0096ad;
        }
        
        .role-info ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .role-info li {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="ui-header">
        <div class="ui-header-logo">
            <a href="../index.php">
                <h1><span class="sho">Sho</span><span class="chor">hochor</span></h1>
            </a>
        </div>
    </div>
    
    <div class="login-container">
        <div class="form-heading">
            <h1>Welcome Back to Shohochor</h1>
            <p>Sign in to access your elderly care platform</p>
        </div>
        
        <div class="login-form">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['reset_success'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['reset_success']; unset($_SESSION['reset_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['verification_success'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['verification_success']; unset($_SESSION['verification_success']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Enter your password">
                </div>
                
                <div class="form-options">
                    <div class="checkbox-container">
                        <input type="checkbox" id="remember_me" name="remember_me" 
                               <?php echo $remember_me ? 'checked' : ''; ?>>
                        <label for="remember_me">Remember me for 30 days</label>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="./forgot_password.php">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Sign In</button>
                
                <div class="role-info">
                    <p><small>Access will be granted based on your registered role:</small></p>
                    <ul>
                        <li><strong>Senior:</strong> Personal health dashboard</li>
                        <li><strong>Caregiver:</strong> Client management system</li>
                        <li><strong>Family Member:</strong> Family care overview</li>
                        <li><strong>Healthcare Provider:</strong> Patient management</li>
                    </ul>
                </div>
                
                <div class="signup-link">
                    <p>Don't have an account? <a href="../signup_user/signup_user.php">Sign up here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>