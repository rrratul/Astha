<?php
// Get user information with proper null checks
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
$user_full_name = trim("$user_first_name $user_last_name");
if (empty($user_full_name)) $user_full_name = 'User';

$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
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
.mobile-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: white;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    z-index: 999;
    display: none;
}

.mobile-nav-items {
    display: flex;
    justify-content: space-around;
    align-items: center;
    width: 100%;
    padding: 10px 0;
}

.mobile-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #718096;
    text-decoration: none;
    font-size: 0.8rem;
    width: 20%; /* Each item takes equal space */
}

.mobile-nav-item i {
    font-size: 1.4rem;
    margin-bottom: 5px;
}

.mobile-nav-item.active {
    color: var(--primary-color, #0096ad);
}

/* Show mobile nav on smaller screens */
@media (max-width: 768px) {
    .mobile-nav {
        display: block;
    }
    
    body {
        padding-bottom: 70px; /* Add padding to prevent content being hidden by nav */
    }
}
</style>

<div class="mobile-nav">
    <div class="mobile-nav-items">
        <a href="../relative/index.php" class="mobile-nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="../relative/health-dashboard.php" class="mobile-nav-item <?php echo (strpos($current_page, 'health') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-heartbeat"></i>
            <span>Health</span>
        </a>
        <a href="../relative/communication-center.php" class="mobile-nav-item <?php echo (strpos($current_page, 'communication') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i>
            <span>Connect</span>
        </a>
        <a href="../relative/financial-center.php" class="mobile-nav-item <?php echo (strpos($current_page, 'financial') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Finance</span>
        </a>
        <a href="../relative/profile.php" class="mobile-nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</div>