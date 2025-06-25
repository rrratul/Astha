<?php
session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';
require_once 'db_connect.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'healthcare_provider') {
    header("Location: ../logout.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Healthcare Provider';
$user_first_name = $_SESSION['user_first_name'] ?? 'Healthcare Provider';

// Get healthcare provider details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$provider = $stmt->fetch(PDO::FETCH_ASSOC);

// Get specializations
$stmt = $pdo->prepare("
    SELECT s.name 
    FROM specializations s
    JOIN healthcare_provider_specializations hps ON s.id = hps.specialization_id
    WHERE hps.provider_id = :provider_id
");
$stmt->execute(['provider_id' => $user_id]);
$specializations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Basic validation
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $qualification = trim($_POST['qualification']);
        $experience_years = (int)$_POST['experience_years'];
        $bio = trim($_POST['bio']);
        $consultation_fee = (float)$_POST['consultation_fee'];
        
        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $_SESSION['error_message'] = "Name and email are required fields.";
        } else {
            try {
                // Begin transaction
                $pdo->beginTransaction();
                
                // Update user table
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        email = :email, 
                        phone = :phone,
                        bio = :bio
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'bio' => $bio,
                    'id' => $user_id
                ]);
                
                // Update healthcare_providers table
                $stmt = $pdo->prepare("
                    UPDATE healthcare_providers 
                    SET qualification = :qualification, 
                        experience_years = :experience_years,
                        consultation_fee = :consultation_fee
                    WHERE user_id = :user_id
                ");
                
                $stmt->execute([
                    'qualification' => $qualification,
                    'experience_years' => $experience_years,
                    'consultation_fee' => $consultation_fee,
                    'user_id' => $user_id
                ]);
                
                // Upload profile picture if provided
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (in_array($_FILES['profile_picture']['type'], $allowed)) {
                        $image_data = file_get_contents($_FILES['profile_picture']['tmp_name']);
                        $image_type = $_FILES['profile_picture']['type'];
                        
                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET profile_picture = :profile_picture,
                                profile_picture_type = :profile_picture_type
                            WHERE id = :id
                        ");
                        
                        $stmt->execute([
                            'profile_picture' => $image_data,
                            'profile_picture_type' => $image_type,
                            'id' => $user_id
                        ]);
                    } else {
                        $_SESSION['error_message'] = "Invalid image format. Please upload JPG, PNG, or GIF.";
                        $pdo->rollBack();
                        header("Location: profile.php");
                        exit();
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                // Update session variables
                $_SESSION['user_first_name'] = $first_name;
                $_SESSION['user_name'] = "$first_name $last_name";
                
                $_SESSION['success_message'] = "Profile updated successfully!";
                header("Location: profile.php");
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Password change logic
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate password
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error_message'] = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $_SESSION['error_message'] = "Password must be at least 8 characters long.";
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $current_hash = $stmt->fetchColumn();
            
            if (password_verify($current_password, $current_hash)) {
                // Update password
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute([
                    'password' => $new_hash,
                    'id' => $user_id
                ]);
                
                $_SESSION['success_message'] = "Password changed successfully!";
            } else {
                $_SESSION['error_message'] = "Current password is incorrect.";
            }
        }
        
        header("Location: profile.php");
        exit();
    }
}

// Get healthcare provider specific details
$stmt = $pdo->prepare("
    SELECT * FROM healthcare_providers 
    WHERE user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$provider_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Example to add specializations to a provider
try {
    // First get specialization IDs
    $stmt = $pdo->prepare("SELECT id FROM specializations WHERE name IN ('Cardiology', 'Internal Medicine')");
    $stmt->execute();
    $specializations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add each specialization to the provider
    foreach ($specializations as $spec_id) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO healthcare_provider_specializations 
            (provider_id, specialization_id) 
            VALUES (:provider_id, :specialization_id)
        ");
        $stmt->execute([
            'provider_id' => $user_id,
            'specialization_id' => $spec_id
        ]);
    }
    
    echo "Specializations added successfully!";
} catch (PDOException $e) {
    echo "Error adding specializations: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Healthcare Provider Dashboard</title>
    
    <!-- Google Translate -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            // Initialize desktop translate
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            // Initialize mobile translate separately
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'mobile-google-translate');
        }
    </script>
    
    <!-- Styles and fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
    
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            color: #0096ad;
            margin-right: 12px;
        }
        
        .profile-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .profile-sidebar {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 600;
            color: #0096ad;
            overflow: hidden;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h2 {
            font-size: 22px;
            margin: 0 0 5px;
            font-weight: 600;
        }
        
        .profile-specialization {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-stats {
            padding: 20px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2f5f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0096ad;
            margin-right: 15px;
        }
        
        .stat-details {
            flex: 1;
        }
        
        .stat-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 2px;
        }
        
        .stat-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .profile-forms {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .form-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .form-title i {
            color: #0096ad;
            margin-right: 10px;
        }
        
        .form-body {
            padding: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #0096ad;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 150, 173, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-note {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        
        .form-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }
        
        .btn-submit {
            background-color: #0096ad;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #007a8c;
        }
        
        .specialization-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .specialization-tag {
            background-color: #e2f5f8;
            color: #0096ad;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Alert messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #822727;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        @media (max-width: 900px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="profile-container">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-md"></i>
                    My Profile
                </h1>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="profile-content">
                <div class="profile-sidebar">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if (!empty($provider['profile_picture']) && !empty($provider['profile_picture_type'])): ?>
                                <img src="data:<?php echo $provider['profile_picture_type']; ?>;base64,<?php echo base64_encode($provider['profile_picture']); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <?php echo strtoupper(substr($provider['first_name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <h2>Dr. <?php echo htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']); ?></h2>
                            <div class="profile-specialization">
                                <?php echo !empty($specializations) ? htmlspecialchars(implode(', ', $specializations)) : 'Healthcare Provider'; ?>
                            </div>
                            <div class="profile-badge">
                                Verified Provider
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="stat-details">
                                <div class="stat-label">Experience</div>
                                <div class="stat-value">
                                    <?php echo !empty($provider_details['experience_years']) ? $provider_details['experience_years'] . ' years' : 'Not specified'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-details">
                                <div class="stat-label">Appointments</div>
                                <div class="stat-value">
                                    <?php 
                                    // Get appointment count
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_name = :doctor_name");
                                    $stmt->execute(['doctor_name' => $user_name]);
                                    $appointment_count = $stmt->fetchColumn();
                                    echo $appointment_count;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-details">
                                <div class="stat-label">Rating</div>
                                <div class="stat-value">
                                    <?php 
                                    // Get average rating
                                    $stmt = $pdo->prepare("
                                        SELECT AVG(rating) 
                                        FROM reviews 
                                        WHERE doctor_id = :doctor_id
                                    ");
                                    $stmt->execute(['doctor_id' => $user_id]);
                                    $avg_rating = $stmt->fetchColumn();
                                    echo $avg_rating ? number_format($avg_rating, 1) . '/5.0' : 'No ratings yet';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-details">
                                <div class="stat-label">Consultation Fee</div>
                                <div class="stat-value">
                                    <?php echo !empty($provider_details['consultation_fee']) ? '৳' . number_format($provider_details['consultation_fee'], 2) : 'Not set'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-forms">
                    <div class="form-card">
                        <div class="form-header">
                            <h2 class="form-title">
                                <i class="fas fa-user-edit"></i>
                                Personal Information
                            </h2>
                        </div>
                        
                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($provider['first_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($provider['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($provider['email']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($provider['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="qualification">Qualification</label>
                                        <input type="text" id="qualification" name="qualification" class="form-control" 
                                            value="<?php echo htmlspecialchars($provider_details['qualification'] ?? ''); ?>">
                                        <div class="form-note">E.g., MBBS, MD, PhD</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="experience_years">Years of Experience</label>
                                        <input type="number" id="experience_years" name="experience_years" class="form-control" min="0" max="70" 
                                            value="<?php echo htmlspecialchars($provider_details['experience_years'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="consultation_fee">Consultation Fee (৳)</label>
                                    <input type="number" id="consultation_fee" name="consultation_fee" class="form-control" min="0" step="0.01"
                                        value="<?php echo htmlspecialchars($provider_details['consultation_fee'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="bio">Biography</label>
                                    <textarea id="bio" name="bio" class="form-control"><?php echo htmlspecialchars($provider['bio'] ?? ''); ?></textarea>
                                    <div class="form-note">A brief description of your professional experience and approach</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="profile_picture">Profile Picture</label>
                                    <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                                    <div class="form-note">Recommended size: 300x300px, Max size: 2MB</div>
                                </div>
                            </div>
                            
                            <div class="form-footer">
                                <button type="submit" name="update_profile" class="btn-submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="form-card">
                        <div class="form-header">
                            <h2 class="form-title">
                                <i class="fas fa-lock"></i>
                                Change Password
                            </h2>
                        </div>
                        
                        <form action="profile.php" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="form-label" for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-footer">
                                <button type="submit" name="change_password" class="btn-submit">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const profileForm = document.querySelector('form[name="update_profile"]');
            const passwordForm = document.querySelector('form[name="change_password"]');
            
            if (profileForm) {
                profileForm.addEventListener('submit', function(event) {
                    const firstName = document.getElementById('first_name').value.trim();
                    const lastName = document.getElementById('last_name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    
                    if (!firstName || !lastName || !email) {
                        event.preventDefault();
                        alert('Name and email are required fields.');
                    }
                });
            }
            
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(event) {
                    const currentPassword = document.getElementById('current_password').value;
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        event.preventDefault();
                        alert('All password fields are required.');
                    } else if (newPassword !== confirmPassword) {
                        event.preventDefault();
                        alert('New passwords do not match.');
                    } else if (newPassword.length < 8) {
                        event.preventDefault();
                        alert('Password must be at least 8 characters long.');
                    }
                });
            }
        });
    </script>
</body>
</html>