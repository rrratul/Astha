<?php
// Get user information if it exists in session
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
$user_full_name = trim("$user_first_name $user_last_name");
if (empty($user_full_name)) $user_full_name = 'User';

$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Family Member';
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : '';

// Set a default profile image if none exists
if (empty($profile_image)) {
    $user_initial = strtoupper(substr($user_first_name, 0, 1));
    $profile_image = "https://via.placeholder.com/60x60/667eea/ffffff?text=" . $user_initial;
}

// Define current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Modern Header and Navigation Styles */
.ui-header {
    background-color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid #edf2f7;
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

.ui-header-logo {
    display: flex;
    align-items: center;
}

.logo {
    font-size: 1.5rem;
    font-weight: 800;
    display: flex;
    letter-spacing: -0.5px;
    text-decoration: none;
}

.sho {
    color: #FF8C00;
}

.hochor {
    color: #00a0bc;
}

/* Main Navigation */
.main-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-grow: 1;
    margin-left: 2rem;
}

.nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.5rem;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    color: #4a5568;
    text-decoration: none;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-link:hover {
    background-color: #f8fafc;
    color: #00a0bc;
}

.nav-link.active {
    background-color: rgba(0, 160, 188, 0.1);
    color: #00a0bc;
    font-weight: 600;
}

.nav-link i,
.nav-link svg {
    margin-right: 8px;
    font-size: 1.1rem;
}

/* User Profile Section */
.user-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-profile {
    position: relative;
    cursor: pointer;
}

.user-menu-toggle {
    display: flex;
    align-items: center;
    background: none;
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.user-menu-toggle:hover {
    background-color: #f8fafc;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 10px;
    background-color: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.user-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.95rem;
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    color: #718096;
}

/* User Dropdown Menu */
.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 0;
    margin-top: 0.5rem;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 100;
    border: 1px solid #edf2f7;
}

.user-profile:hover .user-dropdown,
.user-menu-toggle:focus + .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid #edf2f7;
}

.dropdown-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.dropdown-email {
    font-size: 0.85rem;
    color: #718096;
    word-break: break-all;
}

.dropdown-items {
    padding: 0.5rem 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #4a5568;
    text-decoration: none;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8fafc;
    color: #00a0bc;
}

.dropdown-item i,
.dropdown-item svg {
    margin-right: 10px;
    font-size: 1rem;
    color: #718096;
    width: 20px;
    text-align: center;
}

.dropdown-divider {
    height: 1px;
    background-color: #edf2f7;
    margin: 0.5rem 0;
}

.dropdown-item.danger {
    color: #e53e3e;
}

.dropdown-item.danger:hover {
    background-color: #fff5f5;
    color: #c53030;
}

.dropdown-item.danger i,
.dropdown-item.danger svg {
    color: #e53e3e;
}

@media (max-width: 992px) {
    .main-nav {
        display: none;
    }
    
    .user-info {
        display: none;
    }
}

@media (max-width: 576px) {
    .header-container {
        padding: 0 0.75rem;
    }
}
</style>

<header class="ui-header">
    <div class="header-container">
        <!-- Logo -->
        <div class="ui-header-logo">
            <a href="../index.php" class="logo">
                <span class="sho">SHO</span><span class="hochor">HOCHOR</span>
            </a>
        </div>
        
        <!-- Main Navigation -->
        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="../relative/index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../relative/health-dashboard.php" class="nav-link <?php echo (strpos($current_page, 'health') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-heartbeat"></i>
                        Health Monitor
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../relative/communication-center.php" class="nav-link <?php echo (strpos($current_page, 'communication') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        Communication
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../relative/financial-center.php" class="nav-link <?php echo (strpos($current_page, 'financial') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-dollar-sign"></i>
                        Financial
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../relative/community-events.php" class="nav-link <?php echo (strpos($current_page, 'community') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        Community
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- User Profile Section -->
        <div class="user-section">
            <div class="user-profile">
                <button class="user-menu-toggle" id="userMenuToggle">
                    <div class="user-avatar">
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="User">
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_first_name); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($user_role); ?></span>
                    </div>
                </button>
                
                <div class="user-dropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-name"><?php echo htmlspecialchars($user_full_name); ?></div>
                        <div class="dropdown-email"><?php echo htmlspecialchars($user_email); ?></div>
                    </div>
                    <div class="dropdown-items">
                        <a href="../relative/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            My Profile
                        </a>
                        <a href="../relative/settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                        <a href="../relative/notifications.php" class="dropdown-item">
                            <i class="fas fa-bell"></i>
                            Notifications
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../../../Astha/relative/includes/logout.php" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// Toggle dropdown with JavaScript for better accessibility
document.getElementById('userMenuToggle').addEventListener('click', function() {
    const dropdown = this.nextElementSibling;
    dropdown.style.opacity = dropdown.style.opacity === '1' ? '0' : '1';
    dropdown.style.visibility = dropdown.style.opacity === '0' ? 'hidden' : 'visible';
    dropdown.style.transform = dropdown.style.opacity === '0' ? 'translateY(-10px)' : 'translateY(0)';
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.user-profile')) {
        const dropdown = document.querySelector('.user-dropdown');
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.transform = 'translateY(-10px)';
    }
});
</script>