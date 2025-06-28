<?php
session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';

// Simple authentication check
if (!isLoggedIn()) {
    header("Location: ../login_user/login_user.php");
    exit();
}

// Check role
if (isset($_SESSION['user_role'])) {
    $allowed_roles = ['senior', 'elderly']; 
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        switch ($_SESSION['user_role']) {
            case 'caregiver':
                header("Location: ../caregiver/index.php");
                exit();
            case 'family_member':
                header("Location: ../family/index.php");
                exit();
            case 'healthcare_provider':
                header("Location: ../healthcare/index.php");
                exit();
            default:
                header("Location: logout.php");
                exit();
        }
    }
}

// Initialize variables
$user_data = [];
$errors = [];
$success_message = '';
$user_first_name = $_SESSION['user_first_name'] ?? 'User';
$user_role = $_SESSION['user_role'] ?? 'elderly';

// Debug session information
if (empty($_SESSION['user_id'])) {
    $errors[] = "No user ID in session. Please log in again.";
} else {
    error_log("Debug - User ID from session: " . $_SESSION['user_id']);
}

// Get user data from database
if (!empty($_SESSION['user_id']) && $conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            error_log("Debug - User data retrieved successfully for ID: " . $_SESSION['user_id']);
            
            // Log what fields we actually have in the database
            $available_fields = array_keys($user_data);
            error_log("Debug - Available database fields: " . implode(', ', $available_fields));
            
        } else {
            // If no user found in database, check what tables exist
            $tables_result = $conn->query("SHOW TABLES");
            $tables = [];
            while ($row = $tables_result->fetch_array()) {
                $tables[] = $row[0];
            }
            error_log("Debug - Available tables: " . implode(', ', $tables));
            
            $errors[] = "User not found in database. User ID: " . $_SESSION['user_id'];
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $errors[] = "Database error: " . $e->getMessage();
        error_log("Profile page database error: " . $e->getMessage());
    }
}

// Create default user data structure with proper field mapping
$default_user_data = [
    'id' => $_SESSION['user_id'] ?? 0,
    'first_name' => $_SESSION['user_first_name'] ?? '',
    'last_name' => $_SESSION['user_last_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'phone' => '', // This might be in database or session
    'date_of_birth' => '', // This might be 'dob' in database
    'gender' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'country' => '',
    'bio' => '',
    'profile_image' => '',
    'profile_picture' => null,
    'profile_picture_type' => '',
    'profile_picture_name' => '',
    'role' => $_SESSION['user_role'] ?? 'elderly',
    'status' => 'active',
    'email_verified' => $_SESSION['email_verified'] ?? 0,
    'last_login' => $_SESSION['last_login'] ?? null,
    'created_at' => $_SESSION['created_at'] ?? date('Y-m-d H:i:s')
];

// If we have database data, merge it properly
if (!empty($user_data)) {
    // Handle field name mapping from signup (dob vs date_of_birth)
    if (isset($user_data['dob']) && empty($user_data['date_of_birth'])) {
        $user_data['date_of_birth'] = $user_data['dob'];
    }
    
    // Merge database data with defaults, keeping database values where they exist
    foreach ($default_user_data as $key => $default_value) {
        if (!isset($user_data[$key]) || $user_data[$key] === null || $user_data[$key] === '') {
            $user_data[$key] = $default_value;
        }
    }
} else {
    // Use defaults if no database data
    $user_data = $default_user_data;
    if (empty($errors)) {
        $errors[] = "Using session data - database connection may have issues.";
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture']) && $conn) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Please upload a valid image file (JPEG, PNG, GIF, or WebP).";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size must be less than 5MB.";
        } else {
            try {
                $image_data = file_get_contents($file['tmp_name']);
                $image_type = $file['type'];
                $image_name = $file['name'];
                
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ?, profile_picture_type = ?, profile_picture_name = ? WHERE id = ?");
                $stmt->bind_param("sssi", $image_data, $image_type, $image_name, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success_message = "Profile picture uploaded successfully!";
                    // Update user data
                    $user_data['profile_picture'] = $image_data;
                    $user_data['profile_picture_type'] = $image_type;
                    $user_data['profile_picture_name'] = $image_name;
                    
                    // Refresh the page to show new image
                    header("Location: " . $_SERVER['PHP_SELF'] . "?uploaded=1");
                    exit();
                } else {
                    $errors[] = "Failed to upload profile picture: " . $conn->error;
                }
                $stmt->close();
                
            } catch (Exception $e) {
                $errors[] = "Upload error: " . $e->getMessage();
            }
        }
    } else {
        $file_error = $_FILES['profile_picture']['error'] ?? 'No file selected';
        $errors[] = "Please select a valid file to upload. Error: " . $file_error;
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && $conn) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if we need to add missing columns to the database
            $columns_to_check = ['phone', 'date_of_birth', 'gender', 'address', 'city', 'state', 'postal_code', 'country', 'bio'];
            
            foreach ($columns_to_check as $column) {
                $column_check = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
                if ($column_check->num_rows == 0) {
                    // Column doesn't exist, add it
                    $alter_sql = "ALTER TABLE users ADD COLUMN $column TEXT";
                    $conn->query($alter_sql);
                    error_log("Added column: $column to users table");
                }
            }
            
            $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, date_of_birth = ?, gender = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ?, bio = ? WHERE id = ?");
            $update_stmt->bind_param("sssssssssssi", $first_name, $last_name, $phone, $date_of_birth, $gender, $address, $city, $state, $postal_code, $country, $bio, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $_SESSION['user_first_name'] = $first_name;
                $_SESSION['user_last_name'] = $last_name;
                $user_first_name = $first_name;
                
                $success_message = "Profile updated successfully!";
                
                // Update local user data
                $user_data['first_name'] = $first_name;
                $user_data['last_name'] = $last_name;
                $user_data['phone'] = $phone;
                $user_data['date_of_birth'] = $date_of_birth;
                $user_data['gender'] = $gender;
                $user_data['address'] = $address;
                $user_data['city'] = $city;
                $user_data['state'] = $state;
                $user_data['postal_code'] = $postal_code;
                $user_data['country'] = $country;
                $user_data['bio'] = $bio;
                
            } else {
                $errors[] = "Failed to update profile: " . $conn->error;
            }
            $update_stmt->close();
        } catch (Exception $e) {
            $errors[] = "Update error: " . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password']) && $conn) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    }
    if (empty($new_password)) {
        $errors[] = "New password is required";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match";
    }
    if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user_pass = $result->fetch_assoc();
                
                if (password_verify($current_password, $user_pass['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $errors[] = "Failed to change password: " . $conn->error;
                    }
                    $update_stmt->close();
                } else {
                    $errors[] = "Current password is incorrect";
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Password change error: " . $e->getMessage();
        }
    }
}

// Check for upload success message
if (isset($_GET['uploaded']) && $_GET['uploaded'] == '1') {
    $success_message = "Profile picture uploaded successfully!";
}

// Get profile image
$profile_image = '';
if (!empty($user_data['profile_picture'])) {
    $image_type = $user_data['profile_picture_type'] ?: 'image/jpeg';
    $image_data = base64_encode($user_data['profile_picture']);
    $profile_image = "data:$image_type;base64,$image_data";
} else if (!empty($user_data['profile_image'])) {
    $profile_image = $user_data['profile_image'];
}

if (empty($profile_image)) {
    $user_initial = strtoupper(substr($user_data['first_name'] ?: $user_first_name ?: 'U', 0, 1));
    $profile_image = "https://via.placeholder.com/150x150/667eea/ffffff?text=" . $user_initial;
}

// Helper function to safely get array value
function safe_value($array, $key, $default = '') {
    $value = isset($array[$key]) && $array[$key] !== null ? $array[$key] : $default;
    return $value === '' ? $default : $value;
}

// Helper function to format date for input field
function format_date_for_input($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management - Shohochor</title>
    <link rel="stylesheet" href="./includes/style.css">
    <link rel="stylesheet" href="./includes/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .profile-avatar {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            border: 4px solid rgba(255,255,255,0.3);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #667eea;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        
        .avatar-upload:hover {
            background: #5a67d8;
            transform: scale(1.1);
        }
        
        .profile-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .profile-header .email {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .profile-header .member-since {
            font-size: 0.95rem;
            opacity: 0.8;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3748;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: #667eea;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
        }
        
        .btn-danger:hover {
            box-shadow: 0 8px 25px rgba(229, 62, 62, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            color: #2d3748;
            font-size: 1.1rem;
            font-weight: 500;
            word-break: break-word;
        }
        
        .password-section {
            grid-column: 1 / -1;
        }
        
        .form-actions {
            margin-top: 2rem;
            text-align: right;
        }

        .upload-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-right: 1rem;
        }

        .file-input {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }

        .file-input-label {
            display: inline-block;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .file-name {
            color: #6c757d;
            font-style: italic;
            margin-left: 1rem;
        }

        .hidden {
            display: none;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .field-note {
            font-size: 0.85rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    <?php include 'includes/mobile_nav.php'; ?> 

    <main>
        <div class="profile-container">
            <?php if (!empty($_GET['debug'])): ?>
                <div class="debug-info">
                    <strong>Debug Information:</strong><br>
                    Session User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?><br>
                    User Data Keys: <?php echo !empty($user_data) ? implode(', ', array_keys($user_data)) : 'No data'; ?><br>
                    First Name: '<?php echo safe_value($user_data, 'first_name'); ?>'<br>
                    Email: '<?php echo safe_value($user_data, 'email'); ?>'<br>
                    Phone: '<?php echo safe_value($user_data, 'phone'); ?>'<br>
                    DOB: '<?php echo safe_value($user_data, 'date_of_birth'); ?>'<br>
                    Address: '<?php echo safe_value($user_data, 'address'); ?>'<br>
                </div>
            <?php endif; ?>

            <!-- Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <p><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture" id="profile-image">
                    <button type="button" class="avatar-upload" onclick="document.getElementById('picture-upload').click();">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <h1><?php echo htmlspecialchars(trim(safe_value($user_data, 'first_name') . ' ' . safe_value($user_data, 'last_name'))); ?></h1>
                <div class="email"><?php echo htmlspecialchars(safe_value($user_data, 'email')); ?></div>
                <div class="member-since">
                    Member since <?php echo safe_value($user_data, 'created_at') ? date('F Y', strtotime($user_data['created_at'])) : 'N/A'; ?>
                </div>
            </div>

            <!-- Profile Picture Upload -->
            <div class="upload-section">
                <h2 class="section-title">
                    <i class="fas fa-image"></i>
                    Profile Picture
                </h2>
                <form method="POST" enctype="multipart/form-data" id="upload-form">
                    <div class="file-input-wrapper">
                        <input type="file" id="picture-upload" name="profile_picture" class="file-input" accept="image/*" onchange="handleFileSelect(this)">
                        <label for="picture-upload" class="file-input-label">
                            <i class="fas fa-upload"></i> Choose Image
                        </label>
                        <span class="file-name" id="file-name">No file selected</span>
                    </div>
                    <button type="submit" name="upload_picture" class="btn btn-secondary hidden" id="upload-btn">
                        <i class="fas fa-save"></i> Upload Picture
                    </button>
                </form>
            </div>

            <div class="profile-grid">
                <!-- Personal Information -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h2>
                    <form method="POST" id="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'first_name')); ?>" required>
                                <div class="field-note">From signup</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'last_name')); ?>" required>
                                <div class="field-note">From signup</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'phone')); ?>"
                                       placeholder="Enter your phone number">
                                <div class="field-note"><?php echo safe_value($user_data, 'phone') ? 'From signup' : 'You can add this information'; ?></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo format_date_for_input(safe_value($user_data, 'date_of_birth')); ?>">
                                <div class="field-note"><?php echo safe_value($user_data, 'date_of_birth') ? 'From signup' : 'You can add this information'; ?></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo safe_value($user_data, 'gender') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo safe_value($user_data, 'gender') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo safe_value($user_data, 'gender') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <div class="field-note">You can add this information</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'city')); ?>"
                                       placeholder="Enter your city">
                                <div class="field-note">You can add this information</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'state')); ?>"
                                       placeholder="Enter your state or province">
                                <div class="field-note">You can add this information</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'postal_code')); ?>"
                                       placeholder="Enter your postal code">
                                <div class="field-note">You can add this information</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" 
                                       value="<?php echo htmlspecialchars(safe_value($user_data, 'country')); ?>"
                                       placeholder="Enter your country">
                                <div class="field-note">You can add this information</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" 
                                      placeholder="Enter your full address"><?php echo htmlspecialchars(safe_value($user_data, 'address')); ?></textarea>
                            <div class="field-note">You can add this information</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars(safe_value($user_data, 'bio')); ?></textarea>
                            <div class="field-note">You can add this information</div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Account Information
                    </h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars(safe_value($user_data, 'email')); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Account Type</div>
                            <div class="info-value"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', safe_value($user_data, 'role')))); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Account Status</div>
                            <div class="info-value"><?php echo htmlspecialchars(ucfirst(safe_value($user_data, 'status', 'Active'))); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Last Login</div>
                            <div class="info-value">
                                <?php echo safe_value($user_data, 'last_login') ? date('M j, Y g:i A', strtotime($user_data['last_login'])) : 'First time login'; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Member Since</div>
                            <div class="info-value">
                                <?php echo safe_value($user_data, 'created_at') ? date('M j, Y', strtotime($user_data['created_at'])) : 'Recently joined'; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Email Verified</div>
                            <div class="info-value">
                                <?php echo safe_value($user_data, 'email_verified') ? '✅ Yes' : '✅ Yes'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="profile-section password-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Change Password
                </h2>
                <form method="POST" id="password-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-danger">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function handleFileSelect(input) {
            const fileName = document.getElementById('file-name');
            const uploadBtn = document.getElementById('upload-btn');
            const profileImage = document.getElementById('profile-image');
            
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
                uploadBtn.classList.remove('hidden');
                
                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                fileName.textContent = 'No file selected';
                uploadBtn.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Password form validation - ONLY for password form
            const passwordForm = document.getElementById('password-form');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match!');
                        return false;
                    }
                    
                    if (newPassword.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long!');
                        return false;
                    }
                });
            }

            // Auto-hide success messages
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>