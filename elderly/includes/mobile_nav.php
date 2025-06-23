<?php
// Get session variables with fallback values
$user_first_name = $_SESSION['user_first_name'] ?? 'User';
$user_last_name = $_SESSION['user_last_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? 'user@example.com';
$user_role = $_SESSION['user_role'] ?? 'elderly';

// Create full name
$user_full_name = trim($user_first_name . ' ' . $user_last_name);
if (empty($user_full_name) || $user_full_name === ' ') {
    $user_full_name = 'User';
}

// Get user profile image (check both BLOB and file path)
$profile_image = '';
if (isset($_SESSION['user_id'])) {
    // Use the local config.php file in the same includes directory
    require_once __DIR__ . '/config.php';
    
    if (isset($conn) && $conn) {
        try {
            // Get both profile_image (file path) and profile_picture (BLOB)
            $stmt = $conn->prepare("SELECT profile_image, profile_picture, profile_picture_type FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user_data = $result->fetch_assoc();
                    
                    // Check for BLOB profile picture first
                    if (!empty($user_data['profile_picture'])) {
                        $image_type = $user_data['profile_picture_type'] ?? 'image/jpeg';
                        $image_data = base64_encode($user_data['profile_picture']);
                        $profile_image = "data:$image_type;base64,$image_data";
                    }
                    // Then check for file path
                    elseif (!empty($user_data['profile_image'])) {
                        $profile_image = $user_data['profile_image'];
                    }
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            // Silently handle database errors
            error_log("Mobile nav database error: " . $e->getMessage());
        }
    }
}

// Default profile image if none found
if (empty($profile_image)) {
    $user_initial = strtoupper(substr($user_first_name, 0, 1));
    $profile_image = "https://via.placeholder.com/60x60/667eea/ffffff?text=" . $user_initial;
}
?>

<amp-sidebar id="sidebar1" layout="nodisplay" side="right">
    <div class="sidebar-header">
        <div class="logo-mobile">
            <span class="sho">Sho</span><span class="chor">hochor</span>
        </div>
        <button on="tap:sidebar1.close" class="close-button" aria-label="Close menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <!-- Add sidebar user profile -->
    <div class="sidebar-user-profile">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="<?php echo htmlspecialchars($user_full_name); ?>" />
        <div class="sidebar-user-info">
            <h3><?php echo htmlspecialchars($user_full_name); ?></h3>
            <p><?php echo htmlspecialchars($user_email); ?></p>
        </div>
    </div>
    
    <!-- Add sidebar navigation -->
    <div class="sidebar-nav">
        <ul>
            <li>
                <a href="./index.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="../health-monitoring/health-monitoring.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    Health Monitoring
                </a>
            </li>
            <li>
                <a href="../medication-tracking/medication-tracking.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    Medication
                </a>
            </li>
            <li>
                <a href="../social_connection/social_connection.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Social
                </a>
            </li>
            <li>
                <a href="./emergency_contact/emergency-contacts.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Assistance
                </a>
            </li>
            <li>
                <a href="./profile.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    My Profile
                </a>
            </li>
            <li>
                <a href="./settings.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </a>
            </li>
            <li>
                <a href="./help.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Help & Support
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Add Google Translate for mobile -->
    <div id="mobile-google-translate" class="mobile-translate-wrapper"></div>
    
    <!-- Add sidebar footer with logout option -->
    <div class="sidebar-footer">
        <a href="./logout.php" class="logout-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Sign Out
        </a>
    </div>
</amp-sidebar>
