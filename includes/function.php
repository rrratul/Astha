<?php

session_start();
require_once '../includes/config.php';
require_once '../includes/function.php';

// Initialize variables
$error = '';
$success = '';
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
    'dob' => '',
    'user_type' => 'senior', // Changed default to senior
    'terms' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $formData['first_name'] = trim($_POST['first_name'] ?? '');
    $formData['last_name'] = trim($_POST['last_name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['password'] = $_POST['password'] ?? '';
    $formData['confirm_password'] = $_POST['confirm_password'] ?? '';
    $formData['dob'] = trim($_POST['dob'] ?? '');
    $formData['user_type'] = $_POST['user_type'] ?? 'senior'; // Changed default to senior
    $formData['terms'] = isset($_POST['terms']) ? 1 : 0;
    
    // Map user type to role
    $roleMap = [
        'senior' => 'elderly',
        'caregiver' => 'caregiver',
        'family' => 'family_member',
        'healthcare_provider' => 'healthcare_provider'
    ];
    $role = $roleMap[$formData['user_type']] ?? 'elderly'; // Changed default to elderly
    
    // Validate form data
    if (empty($formData['first_name'])) {
        $error = "First name is required";
    } elseif (empty($formData['last_name'])) {
        $error = "Last name is required";
    } elseif (empty($formData['email'])) {
        $error = "Email is required";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (empty($formData['password'])) {
        $error = "Password is required";
    } elseif (strlen($formData['password']) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $error = "Passwords do not match";
    } elseif (empty($formData['terms'])) {
        $error = "You must agree to the Terms and Conditions";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email or login.";
        } else {
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Hash the password
            $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
            
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, date_of_birth, role, verification_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            
            $stmt->bind_param("ssssssss", 
                $formData['first_name'],
                $formData['last_name'],
                $formData['email'],
                $hashedPassword,
                $formData['phone'],
                $formData['dob'],
                $role,
                $verificationToken
            );
            
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                
                // Create default user settings
                $settingsStmt = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
                $settingsStmt->bind_param("i", $userId);
                $settingsStmt->execute();
                
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $formData['first_name'] . ' ' . $formData['last_name'];
                $_SESSION['user_email'] = $formData['email'];
                $_SESSION['user_role'] = $role;
                
                // Redirect to home page
                header("Location: index.php");
                exit();
            } else {
                $error = "Error creating account: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" ⚡>
    <!-- Head section remains unchanged -->
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Sign Up - Shohochor Elderly Care Platform</title>
        <meta name="description" content="Create your Shohochor account to access AI-powered solutions for elderly care.">
        <link href="./signup_user.php" rel="canonical">
        
        <!-- AMP required scripts -->
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
        <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
        
        <!-- Critical AMP Boilerplate -->
        <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
        <noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
        
        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/images/favicon/site.webmanifest.json">
        <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
        
        <!-- Stylesheets -->
        <link rel="stylesheet" href="/css/amp-styles.css">
        <link rel="stylesheet" href="/css/signup.css">
    </head>
    <body>


        <script>
            // JavaScript to handle the user type selection styling
            document.addEventListener('DOMContentLoaded', function() {
                // Get all radio inputs
                var radios = document.querySelectorAll('.user-type-radio');
                
                // Add click event to all user type options
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
            });
        </script>
    </body>
</html>