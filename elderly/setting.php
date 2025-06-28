<?php
session_start();

// Include required files
require_once '../login_user/functions.php';

// Simple authentication check
if (!isLoggedIn()) {
    header("Location: ../login_user/login_user.php");
    exit();
}

// Check role - ACCEPT BOTH 'elderly' AND 'senior'
if (isset($_SESSION['user_role'])) {
    $allowed_roles = ['senior', 'elderly']; // Accept both role names
    
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        // Store current page to prevent redirect loops
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page === 'settings.php') {
            // Wrong role, redirect to correct dashboard
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
                    // Unknown role, logout
                    header("Location: logout.php");
                    exit();
            }
        }
    }
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_first_name = $_SESSION['user_first_name'];
$user_role = $_SESSION['user_role'];

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different form submissions based on action
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'notifications':
            // Handle notification settings
            $medication_reminders = isset($_POST['medication_reminders']) ? 1 : 0;
            $appointment_reminders = isset($_POST['appointment_reminders']) ? 1 : 0;
            $emergency_alerts = isset($_POST['emergency_alerts']) ? 1 : 0;
            $health_tips = isset($_POST['health_tips']) ? 1 : 0;
            $family_notifications = isset($_POST['family_notifications']) ? 1 : 0;
            
            // Save to database (placeholder)
            $success_message = "Notification preferences updated successfully!";
            break;
            
        case 'accessibility':
            // Handle accessibility settings
            $font_size = $_POST['font_size'] ?? 'medium';
            $high_contrast = isset($_POST['high_contrast']) ? 1 : 0;
            $voice_assistance = isset($_POST['voice_assistance']) ? 1 : 0;
            $large_buttons = isset($_POST['large_buttons']) ? 1 : 0;
            
            // Save to database (placeholder)
            $success_message = "Accessibility settings updated successfully!";
            break;
            
        case 'emergency':
            // Handle emergency contact settings
            $primary_contact = $_POST['primary_contact'] ?? '';
            $secondary_contact = $_POST['secondary_contact'] ?? '';
            $medical_info = $_POST['medical_info'] ?? '';
            
            // Save to database (placeholder)
            $success_message = "Emergency settings updated successfully!";
            break;
            
        case 'privacy':
            // Handle privacy settings
            $data_sharing = isset($_POST['data_sharing']) ? 1 : 0;
            $location_tracking = isset($_POST['location_tracking']) ? 1 : 0;
            $health_data_sharing = isset($_POST['health_data_sharing']) ? 1 : 0;
            
            // Save to database (placeholder)
            $success_message = "Privacy settings updated successfully!";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
    
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'mobile-google-translate');
        }
    </script>

    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Shohochor - Settings</title>
    <meta name="description" content="Customize your Shohochor experience with personalized settings for notifications, accessibility, privacy and more.">
    <link href="./settings.php" rel="canonical">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/images/favicon/site.webmanifest.json">
    <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./includes/style.css">
    <link rel="stylesheet" href="./includes/dashboard.css">
    
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Open Sans', sans-serif;
        }
        
        .settings-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: linear-gradient(135deg, #0096ad 0%, #00c6fb 100%);
            border-radius: 16px;
            color: white;
        }
        
        .settings-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .settings-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,150,173,0.1);
            transition: all 0.3s ease;
        }
        
        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,150,173,0.15);
        }
        
        .settings-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .settings-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0096ad, #00c6fb);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .settings-card-icon i {
            color: white;
            font-size: 1.5rem;
        }
        
        .settings-card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 1rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0096ad;
            box-shadow: 0 0 0 3px rgba(0,150,173,0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .checkbox-group:hover {
            background: #f1f5f9;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #0096ad;
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
            margin: 0;
            flex-grow: 1;
        }
        
        .checkbox-description {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 5px;
        }
        
        .settings-button {
            background: linear-gradient(135deg, #0096ad, #00c6fb);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .settings-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,150,173,0.3);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #822727;
            border: 1px solid #fc8181;
        }
        
        .accessibility-controls {
            position: fixed;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 999;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 15px 10px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: 2px solid #0096ad;
        }
        
        .accessibility-controls button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 2px solid #0096ad;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0096ad;
            transition: all 0.3s ease;
        }
        
        .accessibility-controls button:hover {
            background: #0096ad;
            color: white;
            transform: scale(1.1);
        }
        
        .settings-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .settings-tab {
            padding: 12px 24px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #4a5568;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .settings-tab.active,
        .settings-tab:hover {
            background: #0096ad;
            color: white;
            border-color: #0096ad;
        }
        
        @media (max-width: 768px) {
            .settings-container {
                padding: 15px;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .settings-card {
                padding: 20px;
            }
            
            .settings-header h1 {
                font-size: 2rem;
            }
            
            .accessibility-controls {
                bottom: 100px;
                left: 10px;
                transform: none;
            }
            
            .settings-tabs {
                justify-content: center;
            }
            
            .settings-tab {
                flex: 1;
                text-align: center;
                min-width: auto;
            }
        }
        
        @media (max-width: 576px) {
            .accessibility-controls button {
                width: 40px;
                height: 40px;
            }
            
            .settings-card-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .settings-card-icon {
                margin-right: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/nav.php'; ?>
    <?php include 'includes/mobile_nav.php'; ?>
    
    <!-- Accessibility Controls -->
    <div class="accessibility-controls">
        <button onclick="increaseFontSize()" title="Increase Text Size">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1">
                <text x="5" y="19" font-size="18" font-weight="bold">A+</text>
            </svg>
        </button>
        <button onclick="decreaseFontSize()" title="Decrease Text Size">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1">
                <text x="5" y="19" font-size="16" font-weight="bold">A-</text>
            </svg>
        </button>
        <button onclick="toggleHighContrast()" title="Toggle High Contrast">
            <i class="fas fa-adjust"></i>
        </button>
        <button onclick="readPageContent()" title="Read Page Content">
            <i class="fas fa-volume-up"></i>
        </button>
    </div>
    
    <main>
        <div class="settings-container">
            <!-- Page Header -->
            <div class="settings-header">
                <h1>Settings & Preferences</h1>
                <p>Customize your Shohochor experience to meet your needs</p>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Settings Grid -->
            <div class="settings-grid">
                <!-- Notification Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h2 class="settings-card-title">Notifications</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="notifications">
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="medication_reminders" name="medication_reminders" checked>
                            <label for="medication_reminders">
                                Medication Reminders
                                <div class="checkbox-description">Get notified when it's time to take your medications</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="appointment_reminders" name="appointment_reminders" checked>
                            <label for="appointment_reminders">
                                Appointment Reminders
                                <div class="checkbox-description">Receive reminders for upcoming medical appointments</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="emergency_alerts" name="emergency_alerts" checked>
                            <label for="emergency_alerts">
                                Emergency Alerts
                                <div class="checkbox-description">Critical health and safety notifications</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="health_tips" name="health_tips" checked>
                            <label for="health_tips">
                                Daily Health Tips
                                <div class="checkbox-description">Receive personalized health and wellness tips</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="family_notifications" name="family_notifications">
                            <label for="family_notifications">
                                Family Notifications
                                <div class="checkbox-description">Allow family members to receive your health updates</div>
                            </label>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Save Notification Settings
                        </button>
                    </form>
                </div>
                
                <!-- Accessibility Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-universal-access"></i>
                        </div>
                        <h2 class="settings-card-title">Accessibility</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="accessibility">
                        
                        <div class="form-group">
                            <label for="font_size">Text Size</label>
                            <select id="font_size" name="font_size">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                                <option value="extra-large">Extra Large</option>
                            </select>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="high_contrast" name="high_contrast">
                            <label for="high_contrast">
                                High Contrast Mode
                                <div class="checkbox-description">Improve visibility with high contrast colors</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="voice_assistance" name="voice_assistance" checked>
                            <label for="voice_assistance">
                                Voice Assistance
                                <div class="checkbox-description">Enable voice commands and audio feedback</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="large_buttons" name="large_buttons">
                            <label for="large_buttons">
                                Large Buttons
                                <div class="checkbox-description">Make buttons and controls easier to tap</div>
                            </label>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Save Accessibility Settings
                        </button>
                    </form>
                </div>
                
                <!-- Emergency Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h2 class="settings-card-title">Emergency Contacts</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="emergency">
                        
                        <div class="form-group">
                            <label for="primary_contact">Primary Emergency Contact</label>
                            <input type="tel" id="primary_contact" name="primary_contact" 
                                   placeholder="+880 1234 567890">
                        </div>
                        
                        <div class="form-group">
                            <label for="secondary_contact">Secondary Emergency Contact</label>
                            <input type="tel" id="secondary_contact" name="secondary_contact" 
                                   placeholder="+880 1234 567890">
                        </div>
                        
                        <div class="form-group">
                            <label for="medical_info">Important Medical Information</label>
                            <textarea id="medical_info" name="medical_info" rows="4" 
                                      placeholder="Allergies, medical conditions, current medications..."></textarea>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Save Emergency Settings
                        </button>
                    </form>
                </div>
                
                <!-- Privacy Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h2 class="settings-card-title">Privacy & Data</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="privacy">
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="data_sharing" name="data_sharing">
                            <label for="data_sharing">
                                Share Data for Research
                                <div class="checkbox-description">Help improve healthcare through anonymous data sharing</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="location_tracking" name="location_tracking" checked>
                            <label for="location_tracking">
                                Location Services
                                <div class="checkbox-description">Enable for emergency services and location-based features</div>
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="health_data_sharing" name="health_data_sharing">
                            <label for="health_data_sharing">
                                Share Health Data with Family
                                <div class="checkbox-description">Allow authorized family members to view your health data</div>
                            </label>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Save Privacy Settings
                        </button>
                    </form>
                </div>
                
                <!-- Language & Region Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h2 class="settings-card-title">Language & Region</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="language">
                        
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language">
                                <option value="en" selected>English</option>
                                <option value="bn">বাংলা (Bengali)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone">Time Zone</label>
                            <select id="timezone" name="timezone">
                                <option value="Asia/Dhaka" selected>Bangladesh Time (GMT+6)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format">
                                <option value="dd/mm/yyyy">DD/MM/YYYY</option>
                                <option value="mm/dd/yyyy" selected>MM/DD/YYYY</option>
                                <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Save Language Settings
                        </button>
                    </form>
                </div>
                
                <!-- Account Security -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="settings-card-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h2 class="settings-card-title">Account Security</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="security">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Enter your current password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Enter a new password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your new password">
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="two_factor" name="two_factor">
                            <label for="two_factor">
                                Enable Two-Factor Authentication
                                <div class="checkbox-description">Add an extra layer of security to your account</div>
                            </label>
                        </div>
                        
                        <button type="submit" class="settings-button">
                            <i class="fas fa-save"></i>
                            Update Security Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="settings-card" style="grid-column: 1 / -1;">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h2 class="settings-card-title">Quick Actions</h2>
                </div>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                    <a href="./profile.php" class="settings-button" style="text-decoration: none;">
                        <i class="fas fa-user"></i>
                        Edit Profile
                    </a>
                    <a href="./backup.php" class="settings-button" style="text-decoration: none; background: linear-gradient(135deg, #27ae60, #2ecc71);">
                        <i class="fas fa-download"></i>
                        Backup Data
                    </a>
                    <a href="./help.php" class="settings-button" style="text-decoration: none; background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-question-circle"></i>
                        Get Help
                    </a>
                    <button onclick="confirmLogout()" class="settings-button" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-sign-out-alt"></i>
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script>
        // Font size adjustment
        function increaseFontSize() {
            document.body.style.fontSize = 
                parseFloat(window.getComputedStyle(document.body).fontSize) * 1.1 + 'px';
            showToast('Text size increased');
        }
        
        function decreaseFontSize() {
            document.body.style.fontSize = 
                parseFloat(window.getComputedStyle(document.body).fontSize) * 0.9 + 'px';
            showToast('Text size decreased');
        }
        
        // High contrast mode
        function toggleHighContrast() {
            document.body.classList.toggle('high-contrast');
            const isEnabled = document.body.classList.contains('high-contrast');
            
            if (isEnabled) {
                document.body.style.backgroundColor = '#000';
                document.body.style.color = '#FFF';
                showToast('High contrast mode enabled');
            } else {
                document.body.style.backgroundColor = '';
                document.body.style.color = '';
                showToast('High contrast mode disabled');
            }
        }
        
        // Read page content
        function readPageContent() {
            if ('speechSynthesis' in window) {
                const mainContent = document.querySelector('main').innerText;
                const cleanedContent = mainContent.replace(/\s+/g, ' ').trim();
                
                const maxLength = 2000;
                const contentToRead = cleanedContent.length > maxLength ? 
                    cleanedContent.substring(0, maxLength) + '...' : cleanedContent;
                
                const utterance = new SpeechSynthesisUtterance(contentToRead);
                utterance.rate = 0.8;
                utterance.pitch = 1;
                utterance.volume = 0.8;
                
                speechSynthesis.speak(utterance);
                showToast('Reading page content');
            } else {
                showToast('Speech synthesis not supported');
            }
        }
        
        // Toast notification
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #0096ad;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-weight: 600;
                animation: slideIn 0.3s ease-out;
            `;
            toast.textContent = message;
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Confirm logout
        function confirmLogout() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = './logout.php';
            }
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const action = form.querySelector('input[name="action"]').value;
                    
                    if (action === 'security') {
                        const newPassword = form.querySelector('#new_password').value;
                        const confirmPassword = form.querySelector('#confirm_password').value;
                        
                        if (newPassword && newPassword !== confirmPassword) {
                            e.preventDefault();
                            showToast('Passwords do not match');
                            return;
                        }
                        
                        if (newPassword && newPassword.length < 8) {
                            e.preventDefault();
                            showToast('Password must be at least 8 characters long');
                            return;
                        }
                    }
                });
            });
            
            // Auto-save functionality for checkboxes
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // You can implement auto-save here
                    console.log(`Setting ${this.name} changed to ${this.checked}`);
                });
            });
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                showToast('Use individual save buttons for each section');
            }
        });
    </script>
</body>
</html>