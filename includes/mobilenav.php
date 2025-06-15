<style>
/* Enhanced Mobile Navigation */
amp-sidebar {
    background-color: white !important;
    z-index: 2147483647 !important;
    max-width: 85% !important;
    box-shadow: 0 0 30px rgba(0,0,0,0.15) !important;
    height: 100% !important;
    overflow-y: auto !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* Logo fix for mobile */
.ui-header-logo {
    min-width: 180px !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

.ui-header-logo h1 {
    white-space: nowrap !important;
    overflow: visible !important;
    min-width: 180px !important;
}

.sho, .chor {
    display: inline-block !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

/* Top authentication buttons */
.auth-buttons {
    display: flex !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-bottom: 1px solid #e5e5e5 !important;
}

.auth-btn {
    flex: 1 !important;
    text-align: center !important;
    padding: 18px 10px !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    text-decoration: none !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s ease !important;
}

.login-btn {
    background-color: #f8f9fa !important;
    color: #0096ad !important;
    border-right: 1px solid #e5e5e5 !important;
}

.signup-btn {
    background-color: #0096ad !important;
    color: white !important;
}

/* Close button */
.close-sidebar {
    position: absolute !important;
    top: 10px !important;
    right: 10px !important;
    background: none !important;
    border: none !important;
    font-size: 24px !important;
    color: #333 !important;
    cursor: pointer !important;
    padding: 5px !important;
    z-index: 9999 !important;
    margin: 0 !important;
    transition: transform 0.3s ease !important;
}

.close-sidebar:hover {
    transform: rotate(90deg) !important;
}

/* Mobile menu items */
amp-sidebar .show_dropdown {
    display: flex !important;
    flex-direction: column !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    margin-top: 0 !important;
}

amp-sidebar .show_dropdown li {
    margin-bottom: 0 !important;
    position: relative !important;
    width: 100% !important;
    text-align: left !important;
    border-bottom: 1px solid #e5e5e5 !important;
}

amp-sidebar .show_dropdown a {
    color: #666 !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    display: block !important;
    font-size: 16px !important;
    letter-spacing: 0.5px !important;
    transition: color 0.3s ease !important;
    padding: 15px !important;
    width: 100% !important;
    background: transparent !important;
    border: none !important;
}

amp-sidebar .show_dropdown a:hover {
    color: #0096ad !important;
}

/* Hamburger menu icon */
.ui-header-ham {
    cursor: pointer !important;
    display: none !important;
    background: transparent !important;
    border: none !important;
    outline: none !important;
    padding: 10px !important;
}

.hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
    height: 3px !important;
    background-color: #0096ad !important;
    border-radius: 3px !important;
}

@media (max-width: 992px) {
    .ui-header-ham {
        display: block !important;
    }
    
    .ui-header-inner {
        padding: 0 20px !important;
    }
    
    .ui-header-navbar-dropdown {
        display: none !important;
    }
}

@media (max-width: 576px) {
    .ui-header-inner {
        padding: 0 15px !important;
    }
    
    .ui-header-ham {
        padding: 8px !important;
    }
    
    .hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
        height: 2px !important;
    }
    
    amp-sidebar .show_dropdown {
        padding: 15px 15px !important;
    }
    
    amp-sidebar .show_dropdown li {
        width: 100% !important; /* Full width on very small screens */
    }
    
    .sidebar-btn {
        padding: 14px !important;
    }
}
</style>

<amp-sidebar id="sidebar1" layout="nodisplay" side="left" style="z-index: 2147483647; opacity: 1; background-color: #fff;">
    <button on="tap:sidebar1.close" class="close-sidebar">✕</button>
    
    <!-- Auth buttons at the very top -->
    <div class="auth-buttons">
        <a href="../login_user/login_user.php" class="auth-btn login-btn">LOGIN</a>
        <a href="../signup_user/signup_user.php" class="auth-btn signup-btn">SIGN UP</a>
    </div>
    
    <ul class="ui-header-navbar-dropdown show_dropdown">
        <!-- Basic navigation for all users -->
        <li class="navbar-dropdown-item-1">
            <a class="ui-textlink-families" href="#services" on="tap:sidebar1.close,services.scrollTo(duration=600)">SENIOR CARE SERVICES</a>
        </li>
        <li class="navbar-dropdown-item-2">
            <a class="ui-textlink-jobs" href="#features" on="tap:sidebar1.close,features.scrollTo(duration=600)">PLATFORM FEATURES</a>
        </li>
        <li class="navbar-dropdown-item-3">
            <a class="ui-textlink-care" href="#contact" on="tap:sidebar1.close,contact.scrollTo(duration=600)">CARE SOLUTIONS</a>
        </li>
        <li class="navbar-dropdown-item-4">
            <a class="ui-textlink-aboutus" href="#about" on="tap:sidebar1.close,about.scrollTo(duration=600)">ABOUT US</a>
        </li>
    </ul>
</amp-sidebar>