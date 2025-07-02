<?php
// Determine current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>.ui-header-inner {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0 30px !important;
        }
        
        /* Logo fix - ensure full text is visible */
        .ui-header-logo {
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
            left: 0 !important;
            min-width: 160px !important;
            overflow: visible !important;
        }
        
        .ui-header-logo a {
            padding: 0 !important;
            margin: 0 !important;
            white-space: nowrap !important;
            overflow: visible !important;
        }
        
        .ui-header-logo h1 {
            padding: 0 !important;
            margin: 0 !important;
            min-width: 180px !important;
            white-space: nowrap !important;
            overflow: visible !important;
        }
        
        /* Ensure both parts of the logo are visible */
        .sho, .chor {
            display: inline-block !important;
            white-space: nowrap !important;
            overflow: visible !important;
        }
        
        .ui-header-navbar {
            margin-left: auto !important;
            padding-right: 0 !important;
        }
        </style
       

<header class="main-header">
    <div class="container">
        <div class="header-wrapper">
           <div class="ui-header-inner">
            <div class="ui-header-logo">
                <a role="link" aria-label="Home" href="../index.php">
                    <h1><span class="sho">Sho</span><span class="chor">hochor</span></h1>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>">
                        <a href="patients.php" class="nav-link">
                            <i class="fas fa-user-injured"></i>
                            <span>Patients</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>">
                        <a href="appointments.php" class="nav-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Appointments</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $current_page == 'health-records.php' ? 'active' : ''; ?>">
                        <a href="health-records.php" class="nav-link">
                            <i class="fas fa-heartbeat"></i>
                            <span>Health Records</span>
                        </a>
                    </li>
                  
                </ul>
            </nav>
            
            <div class="header-controls">
                <div id="google_translate_element"></div>
                
                <div class="notifications dropdown">
                    <button class="notification-btn" id="notificationDropdown">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="dropdown-menu notification-dropdown">
                        <div class="dropdown-header">
                            <h4>Notifications</h4>
                            <a href="notifications.php">View All</a>
                        </div>
                        <div class="notification-list">
                            <a href="#" class="notification-item">
                                <div class="notification-icon urgent">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">Critical blood pressure alert for Fatima Rahman</p>
                                    <span class="notification-time">20 minutes ago</span>
                                </div>
                            </a>
                            <a href="#" class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">New appointment request from Ahmed Hossain</p>
                                    <span class="notification-time">1 hour ago</span>
                                </div>
                            </a>
                            <a href="#" class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">Test results available for patient ID: RH-3462</p>
                                    <span class="notification-time">Yesterday</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="user dropdown">
                    <button class="user-btn" id="userDropdown">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user_first_name, 0, 1)); ?>
                        </div>
                        <span class="user-name">Dr. <?php echo htmlspecialchars($user_first_name); ?></span>
                    </button>
                    <div class="dropdown-menu user-dropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user-md"></i> My Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="./includes/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle mobile menu
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
    
    // Dropdown functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('button');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(item => {
                    if (item !== menu) {
                        item.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                menu.classList.toggle('show');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});
</script>