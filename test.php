<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">
        <link rel="stylesheet" href="../style.css">
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Login - Shohochor Elderly Care Platform</title>
        <meta name="description" content="Login to your Shohochor account to access AI-powered elderly care solutions">
        <link href="./login_user.php" rel="canonical">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/images/favicon/site.webmanifest.json">
        <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
        
        <style>
            /* Main Styles */
            body {
                font-family: 'Poppins', sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f8fafc;
            }
            
            /* Navigation Bar Styles */
            .ui-header {
                background-color: #ffffff;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                padding: 15px 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 100;
            }
            
            .ui-header-logo a {
                text-decoration: none;
            }
            
            .ui-header-logo h1 {
                margin: 0;
            }
            
            .ui-header-navbar {
                display: flex;
                align-items: center;
            }
            
            .ui-header-navbar-dropdown {
                display: flex;
                list-style: none;
                margin: 0;
                padding: 0;
            }
            
            .ui-header-navbar-dropdown li {
                margin: 0 18px;
            }
            
            .ui-header-navbar-dropdown a {
                color: #4e5a60;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
            }
            
            .ui-header-navbar-dropdown a:hover {
                color: #0096ad;
            }
            
            /* Mobile Navigation Styles */
            .ui-header-ham {
                display: none;
                background: none;
                border: none;
                cursor: pointer;
            }
            
            .hamburger-box {
                width: 30px;
                height: 24px;
                display: inline-block;
                position: relative;
            }
            
            .hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
                width: 30px;
                height: 2px;
                background-color: #4e5a60;
                border-radius: 4px;
                position: absolute;
                transition: transform 0.15s ease;
            }
            
            .hamburger-inner {
                top: 50%;
                transform: translateY(-50%);
            }
            
            .hamburger-inner::before {
                content: '';
                top: -10px;
            }
            
            .hamburger-inner::after {
                content: '';
                bottom: -10px;
            }
            
            amp-sidebar {
                width: 280px;
                background-color: #ffffff;
                padding: 30px 20px;
            }
            
            .show_dropdown {
                display: flex;
                flex-direction: column;
                list-style: none;
                padding: 0;
            }
            
            .show_dropdown li {
                margin: 15px 0;
            }
            
            .show_dropdown a {
                color: #4e5a60;
                text-decoration: none;
                font-size: 16px;
                font-weight: 500;
            }
            
            /* Login Form Styles */
            .login-container {
                max-width: 480px;
                margin: 40px auto;
                padding: 30px 20px;
            }
            
            .login-form {
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                padding: 40px;
                margin-top: 30px;
                border: 1px solid rgba(0,0,0,0.05);
            }
            
            .form-heading {
                text-align: center;
                margin-bottom: 40px;
            }
            
            .form-heading h1 {
                font-size: 28px;
                color: #2d3748;
                margin-bottom: 12px;
                font-weight: 600;
            }
            
            .form-heading p {
                color: #718096;
                font-size: 16px;
                font-weight: 300;
                max-width: 400px;
                margin: 0 auto;
            }
            
            .form-group {
                margin-bottom: 24px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #4a5568;
                font-size: 15px;
            }
            
            .form-control {
                width: 100%;
                padding: 14px;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                font-size: 16px;
                transition: all 0.3s ease;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }
            
            .form-control:focus {
                border-color: #0096ad;
                outline: none;
                box-shadow: 0 0 0 3px rgba(0,150,173,0.12);
            }
            
            .checkbox-container {
                display: flex;
                align-items: center;
                margin-bottom: 25px;
            }
            
            .checkbox-container input {
                margin-right: 10px;
                width: 18px;
                height: 18px;
                accent-color: #0096ad;
            }
            
            .checkbox-container label {
                color: #4a5568;
                font-size: 15px;
            }
            
            .submit-btn {
                background-color: #0096ad;
                color: white;
                border: none;
                padding: 15px 20px;
                width: 100%;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px rgba(0,150,173,0.12);
                font-family: 'Poppins', sans-serif;
            }
            
            .submit-btn:hover {
                background-color: #007a8a;
                transform: translateY(-2px);
                box-shadow: 0 6px 10px rgba(0,150,173,0.15);
            }
            
            .forgot-password {
                text-align: right;
                margin-top: 10px;
                margin-bottom: 20px;
            }
            
            .forgot-password a {
                color: #0096ad;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.2s ease;
            }
            
            .forgot-password a:hover {
                color: #007a8a;
                text-decoration: underline;
            }
            
            .signup-link {
                text-align: center;
                margin-top: 30px;
            }
            
            .signup-link p {
                color: #718096;
                font-size: 15px;
            }
            
            .signup-link a {
                color: #0096ad;
                text-decoration: none;
                font-weight: 600;
                transition: color 0.2s ease;
            }
            
            .signup-link a:hover {
                color: #007a8a;
                text-decoration: underline;
            }
            
            .error-message {
                color: #e53e3e;
                background-color: #fff5f5;
                padding: 12px 16px;
                border-radius: 6px;
                margin-bottom: 24px;
                display: none;
                border: 1px solid #fed7d7;
                font-size: 14px;
            }
            
            /* Footer Styles */
            .ui-footer {
                background-color: #2d3748;
                color: white;
                padding: 60px 30px 30px;
                margin-top: 70px;
            }
            
            .ui-footer-content {
                display: flex;
                max-width: 1200px;
                margin: 0 auto;
                justify-content: space-between;
            }
            
            .ui-footer-typography {
                flex: 1;
                max-width: 300px;
            }
            
            .ui-footer-textlink-container {
                display: flex;
                flex: 2;
                justify-content: space-around;
            }
            
            .ui-footer-line {
                width: 1px;
                background-color: rgba(255,255,255,0.1);
                margin: 0 20px;
            }
            
            .ui-footer a {
                color: #e2e8f0;
                text-decoration: none;
                transition: all 0.3s ease;
                font-size: 14px;
            }
            
            .ui-footer a:hover {
                color: #ffc426;
            }
            
            .ui-footer-logo h3 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .ui-footer p {
                color: #a0aec0;
                font-size: 14px;
                line-height: 1.7;
            }
            
            .ui-footer-textlinks ul {
                list-style: none;
                padding: 0;
            }
            
            .ui-footer-textlinks li {
                margin-bottom: 12px;
            }
            
            .ui-footer-textlinks h3 {
                margin-bottom: 20px;
                color: #ffffff;
                font-size: 18px;
                position: relative;
                padding-bottom: 10px;
            }
            
            .ui-footer-textlinks h3:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 40px;
                height: 3px;
                background-color: #0096ad;
            }
            
            /* Signup button style */
            .signup-btn {
                background-color: #0096ad;
                color: white;
                padding: 10px 18px;
                border-radius: 6px;
                display: inline-block;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .signup-btn:hover {
                background-color: #007a8a;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            }
            
            .sho {
                color: #ff9800;
                font: 700 26px 'Poppins', sans-serif;
            }
            
            .chor {
                color: #0096ad;
                font: 700 26px 'Poppins', sans-serif;
            }
            
            /* Responsive Styles */
            @media (max-width: 768px) {
                /* Mobile Navigation */
                .ui-header-navbar-dropdown {
                    display: none;
                }
                
                .ui-header-ham {
                    display: block;
                }
                
                .ui-header {
                    padding: 12px 20px;
                }
                
                .login-form {
                    padding: 30px 25px;
                }
                
                /* Footer Layout */
                .ui-footer-content {
                    flex-direction: column;
                }
                
                .ui-footer-typography {
                    max-width: 100%;
                    margin-bottom: 40px;
                    text-align: center;
                }
                
                .ui-footer-textlink-container {
                    flex-direction: column;
                }
                
                .ui-footer-textlinks {
                    margin-bottom: 30px;
                    text-align: left;
                    padding: 0 15px;
                }
                
                .ui-footer-textlinks h3:after {
                    left: 50%;
                    transform: translateX(-50%);
                }
                
                .ui-footer-textlinks {
                    text-align: center;
                }
                
                .ui-footer-textlinks ul {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                    text-align: left;
                }
                
                .ui-footer-textlinks li {
                    margin-bottom: 8px;
                }
                
                .ui-footer-line {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <header role="banner" class="ui-header">
            <div class="ui-header-logo">
                <a role="link" aria-label="Home" href="../index.php">
                    <h1><span class="sho">Sho</span><span class="chor">hochor</span></h1>
                </a>
            </div>
            <div class="ui-header-navbar">
            <ul class="ui-header-navbar-dropdown">
                <li class="navbar-dropdown-item-1">
                    <a class="ui-textlink-families" href="../index.php#services">SENIOR CARE SERVICES</a>
                </li>
                <li class="navbar-dropdown-item-2">
                    <a class="ui-textlink-jobs" href="../index.php#features">PLATFORM FEATURES</a>
                </li>
                <li class="navbar-dropdown-item-3">
                    <a class="ui-textlink-care" href="../index.php#contact">CONTACT US</a>
                </li>
                <li class="navbar-dropdown-item-4">
                    <a class="ui-textlink-aboutus" href="../index.php#about">ABOUT US</a>
                </li>
                <li class="navbar-dropdown-item-5">
                    <a href="../signup_user/signup_user.php" class="signup-btn">SIGN UP</a>
                </li>
            </ul>
                <button on="tap:sidebar1.toggle" tabindex="0" class="ui-header-ham hamburger hamburger--collapse" type="button">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </header>
        
        <!-- Mobile Sidebar Navigation -->
        <amp-sidebar id="sidebar1" layout="nodisplay" side="left">
            <div style="text-align: right; padding: 10px;">
                <button on="tap:sidebar1.close" style="background: none; border: none; font-size: 24px; color: #4e5a60; cursor: pointer;">✕</button>
            </div>
            <ul class="ui-header-navbar-dropdown show_dropdown">
                <li class="navbar-dropdown-item-1">
                    <a class="ui-textlink-families" href="../index.php#services">SENIOR CARE SERVICES</a>
                </li>
                <li class="navbar-dropdown-item-2">
                    <a class="ui-textlink-jobs" href="../index.php#features">PLATFORM FEATURES</a>
                </li>
                <li class="navbar-dropdown-item-3">
                    <a class="ui-textlink-care" href="../index.php#solutions">CARE SOLUTIONS</a>
                </li>
                <li class="navbar-dropdown-item-4">
                    <a class="ui-textlink-aboutus" href="../index.php#about">ABOUT US</a>
                </li>
                <li style="margin-top: 20px;">
                    <a href="../signup_user/signup_user.php" class="signup-btn" style="display: inline-block; text-align: center; margin-top: 10px;">SIGN UP</a>
                </li>
            </ul>
        </amp-sidebar>
        
        <div class="login-container">
            <div class="form-heading">
                <h1>Welcome Back to Shohochor</h1>
                <p>Login to access your personalized elderly care dashboard</p>
            </div>
            
            <div class="login-form">
                <!-- Display error message if any -->
                <div class="error-message" id="error-message" [style]="errorMessage ? 'display: block' : 'display: none'" [text]="errorMessage">
                    <!-- Error messages will appear here -->
                </div>
                
                <form method="post" action-xhr="process_login.php" target="_top">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="../reset-password/reset-password.php">Forgot password?</a>
                    </div>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="submit-btn">Login</button>
                    
                    <div class="signup-link">
                        <p>Don't have an account? <a href="../signup_user/signup_user.php">Create an account</a></p>
                    </div>
                </form>
            </div>
        </div>
        
        <footer class="ui-footer">
            <div class="ui-footer-content">
                <div class="ui-footer-typography">
                    <div class="ui-footer-logo">
                        <h3><span class="sho">Sho</span><span class="chor">hochor</span></h3>
                    </div>
                    <p>Empowering elderly care through innovative AI solutions and technology.</p>
                    <p>&copy; 2025 Shohochor - All rights reserved</p>
                </div>
                
                <div class="ui-footer-textlink-container">
                    <div class="ui-footer-textlinks">
                        <h3>Services</h3>
                        <ul>
                            <li><a href="../services/remote-monitoring.php">Remote Monitoring</a></li>
                            <li><a href="../services/medication-management.php">Medication Management</a></li>
                            <li><a href="../services/caregiving-coordination.php">Caregiving Coordination</a></li>
                            <li><a href="../services/health-tracking.php">Health Tracking</a></li>
                        </ul>
                    </div>
                    
                    <div class="ui-footer-line"></div>
                    
                    <div class="ui-footer-textlinks">
                        <h3>Company</h3>
                        <ul>
                            <li><a href="../about.php">About Us</a></li>
                            <li><a href="../careers.php">Careers</a></li>
                            <li><a href="../blog.php">Blog</a></li>
                            <li><a href="../press.php">Press</a></li>
                        </ul>
                    </div>
                    
                    <div class="ui-footer-line"></div>
                    
                    <div class="ui-footer-textlinks">
                        <h3>Support</h3>
                        <ul>
                            <li><a href="../contact.php">Contact Us</a></li>
                            <li><a href="../faq.php">FAQ</a></li>
                            <li><a href="../privacy.php">Privacy Policy</a></li>
                            <li><a href="../terms.php">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>