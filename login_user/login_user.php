<!DOCTYPE html>
<html lang="en" ⚡>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Login - Shohochor Elderly Care Platform</title>
        <meta name="description" content="Login to your Shohochor account to access AI-powered elderly care solutions">
        <link href="./login_user.php" rel="canonical">
        
        <!-- AMP required scripts (moved to the correct location) -->
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
        
        <!-- Critical AMP Boilerplate - MUST be in the head section -->
        <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
        <noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
        
        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
        <link rel="manifest" href="/images/favicon/site.webmanifest.json">
        <link rel="mask-icon" href="/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="theme-color" content="#ffffff">
        
        <style amp-custom>
            /* Main Styles */
            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f5f7f9;
            }
            
            /* Navigation Bar Styles */
            .ui-header {
                background-color: #ffffff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                padding: 10px 20px;
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
            
            .sho {
                color: orange;
                font: 2em sans-serif;
            }
            
            .chor {
                color: #0096ad;
                font: 2em sans-serif;
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
                margin: 0 15px;
            }
            
            .ui-header-navbar-dropdown a {
                color: #4e5a60;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                transition: color 0.2s ease;
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
                outline: none;
            }
            
            .hamburger-box {
                width: 30px;
                height: 24px;
                display: inline-block;
                position: relative;
            }
            
            .hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
                width: 30px;
                height: 3px;
                background-color: #4e5a60;
                border-radius: 3px;
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
            
            /* Fixed Sidebar Styling */
            amp-sidebar {
                background-color: white;
                min-width: 250px;
                max-width: 85%;
                width: 300px;
                z-index: 9999;
            }
            
            /* Fix for the dark overlay mask */
            .i-amphtml-sidebar-mask {
                background-color: rgba(0,0,0,0.5) !important;
                opacity: 1 !important;
                z-index: 9000 !important;
            }
            
            /* Login Form Styles */
            .login-container {
                max-width: 500px;
                margin: 0 auto;
                padding: 30px 20px;
            }
            
            .login-form {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                padding: 30px;
                margin-top: 20px;
            }
            
            .form-heading {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .form-heading h1 {
                font-size: 28px;
                color: #4e5a60;
                margin-bottom: 10px;
            }
            
            .form-heading p {
                color: #6c767f;
                font-size: 16px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #4e5a60;
            }
            
            .form-control {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }
            
            .form-control:focus {
                border-color: #0096ad;
                outline: none;
            }
            
            .checkbox-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .checkbox-container input {
                margin-right: 10px;
            }
            
            .submit-btn {
                background-color: #0096ad;
                color: white;
                border: none;
                padding: 14px 20px;
                width: 100%;
                border-radius: 4px;
                font-size: 16px;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            
            .submit-btn:hover {
                background-color: #007a8a;
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
            }
            
            .forgot-password a:hover {
                text-decoration: underline;
            }
            
            .signup-link {
                text-align: center;
                margin-top: 20px;
            }
            
            .signup-link a {
                color: #0096ad;
                text-decoration: none;
            }
            
            .signup-link a:hover {
                text-decoration: underline;
            }
            
            .error-message {
                color: #e74c3c;
                background-color: #fdedec;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 20px;
                display: none;
            }
            
            /* Footer Styles */
            .ui-footer {
                background-color: #4e5a60;
                color: white;
                padding: 40px 20px 20px;
                margin-top: 50px;
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
                background-color: rgba(255,255,255,0.2);
                margin: 0 20px;
            }
            
            .ui-footer a {
                color: white;
                text-decoration: none;
                transition: color 0.2s ease;
            }
            
            .ui-footer a:hover {
                text-decoration: underline;
                color: #ffc426;
            }
            
            .ui-footer-logo h3 {
                font-size: 24px;
                margin-bottom: 15px;
            }
            
            .ui-footer-textlinks ul {
                list-style: none;
                padding: 0;
            }
            
            .ui-footer-textlinks li {
                margin-bottom: 10px;
            }
            
            .ui-footer-textlinks h3 {
                margin-bottom: 15px;
                color: #ffffff;
                font-size: 18px;
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
                
                /* Footer Layout */
                .ui-footer-content {
                    flex-direction: column;
                }
                
                .ui-footer-typography {
                    max-width: 100%;
                    margin-bottom: 30px;
                    text-align: center;
                }
                
                .ui-footer-textlink-container {
                    flex-direction: column;
                }
                
                .ui-footer-textlinks {
                    margin-bottom: 25px;
                    text-align: left;
                    padding: 0 15px;
                }
                
                .ui-footer-textlinks ul {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
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
                        <a href="../signup_user/signup_user.php" style="background-color: #0096ad; color: white; padding: 8px 15px; border-radius: 4px; display: inline-block; text-decoration: none;">SIGN UP</a>
                    </li>
                </ul>
                <button on="tap:sidebar1.toggle" tabindex="0" class="ui-header-ham hamburger hamburger--collapse" type="button" role="button" aria-label="Menu">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </header>
        
        <!-- FIXED Mobile Navigation -->
        <amp-sidebar id="sidebar1" layout="nodisplay" side="left">
            <div style="padding: 15px; background-color: #ffffff; height: 100%; box-sizing: border-box;">
                <!-- Close Button -->
                <div style="text-align: right; margin-bottom: 20px; background-color: #ffffff;">
                    <button on="tap:sidebar1.close" style="background: none; border: none; font-size: 24px; color: #333333; cursor: pointer; padding: 5px 10px;">✕</button>
                </div>
                
                <!-- Navigation Links -->
                <nav style="background-color: #ffffff;">
                    <ul style="list-style: none; padding: 0; margin: 0; background-color: #ffffff;">
                        <li style="margin-bottom: 20px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; background-color: #ffffff;">
                            <a href="../index.php#services" style="color: #333333; text-decoration: none; font-weight: 700; font-size: 16px; display: block; padding: 8px 0; background-color: #ffffff;">SENIOR CARE SERVICES</a>
                        </li>
                        <li style="margin-bottom: 20px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; background-color: #ffffff;">
                            <a href="../index.php#features" style="color: #333333; text-decoration: none; font-weight: 700; font-size: 16px; display: block; padding: 8px 0; background-color: #ffffff;">PLATFORM FEATURES</a>
                        </li>
                        <li style="margin-bottom: 20px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; background-color: #ffffff;">
                            <a href="../index.php#solutions" style="color: #333333; text-decoration: none; font-weight: 700; font-size: 16px; display: block; padding: 8px 0; background-color: #ffffff;">CARE SOLUTIONS</a>
                        </li>
                        <li style="margin-bottom: 20px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; background-color: #ffffff;">
                            <a href="../index.php#about" style="color: #333333; text-decoration: none; font-weight: 700; font-size: 16px; display: block; padding: 8px 0; background-color: #ffffff;">ABOUT US</a>
                        </li>
                        <li style="margin-bottom: 20px; background-color: #ffffff;">
                            <a href="../index.php#contact" style="color: #333333; text-decoration: none; font-weight: 700; font-size: 16px; display: block; padding: 8px 0; background-color: #ffffff;">CONTACT US</a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Sign Up Button -->
                <div style="margin-top: 30px; background-color: #ffffff;">
                    <a href="../signup_user/signup_user.php" style="background-color: #0096ad; color: white; padding: 12px; border-radius: 4px; display: block; text-align: center; text-decoration: none; font-weight: 600; font-size: 16px;">SIGN UP</a>
                </div>
            </div>
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
        
        <?php include '../includes/footer.php'; ?>
    </body>
</html>