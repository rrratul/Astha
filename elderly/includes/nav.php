<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shohochor_db";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Don't expose database errors to users
    error_log("Database connection error: " . $e->getMessage());
    $conn = null;
}
?><?php
// Use the local config.php file in the same includes directory
require_once __DIR__ . '/config.php';

// Get user information from session
$user_first_name = $_SESSION['user_first_name'] ?? 'User';
$user_last_name = $_SESSION['user_last_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'elderly';

// Create full name
$user_full_name = trim($user_first_name . ' ' . $user_last_name);
if (empty($user_full_name)) {
    $user_full_name = 'User';
}

// Get profile image if available
$profile_image = '';
if (isset($_SESSION['user_id']) && isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT profile_picture, profile_picture_type FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            if (!empty($user_data['profile_picture'])) {
                $image_type = $user_data['profile_picture_type'] ?? 'image/jpeg';
                $image_data = base64_encode($user_data['profile_picture']);
                $profile_image = "data:$image_type;base64,$image_data";
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        // Silently handle database errors
    }
}

// Default profile image if none found
if (empty($profile_image)) {
    $user_initial = strtoupper(substr($user_first_name, 0, 1));
    $profile_image = "https://via.placeholder.com/60x60/667eea/ffffff?text=" . $user_initial;
}
?>


<header role="banner" class="ui-header">
            <div class="header-container">
                <div class="ui-header-logo">
                    <a role="link" aria-label="Home" href="./index.php" style="text-decoration: none;">
                        <div class="logo">
                            <span class="sho">Sho</span><span class="chor">hochor</span>
                        </div>
                    </a>
                </div>
                
                <nav class="ui-header-navbar" aria-label="Main navigation">
                    <ul class="ui-header-nav-menu">
                        <li class="nav-item">
                            <a class="nav-link health-link" href="../health-monitoring/health-monitoring.php">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                HEALTH
                            </a>
                        </li>
                      
                        <li class="nav-item">
                            <a class="nav-link medication-link" href="../medication-tracking/medication-tracking.php">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                                MEDICATION
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link social-link" href="../social_connection/social_connection.php">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                SOCIAL
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link assistance-link" href="../assistance/index.php">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                                ASSISTANCE
                            </a>
                        </li>
                    </ul>
                    
                    <div id="google_translate_element"></div>
                    
                    <!-- Mobile menu button -->
                    <button class="mobile-menu-toggle" on="tap:sidebar1.toggle" aria-label="Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </nav>
                
                <div class="header-actions">
                    <div class="user-account">
                        <div class="user-dropdown">
                            <button class="user-dropdown-button" aria-haspopup="true" aria-expanded="false">
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="User profile" />
                                <span class="user-name"><?php echo htmlspecialchars($user_first_name); ?></span>
                                <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="user-dropdown-content" role="menu">
                                <div class="user-dropdown-header">
                                    <div class="user-dropdown-profile">
                                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="User profile" />
                                        <div class="user-dropdown-info">
                                            <h4><?php echo htmlspecialchars($user_full_name); ?></h4>
                                            <p><?php echo htmlspecialchars($user_email); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <a href="./profile.php" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="./settings.php" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <a href="./help.php" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                    Help & Support
                                </a>
                                <div class="dropdown-divider"></div>
                                
                                <a href="./logout.php" role="menuitem" class="logout-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </nav>