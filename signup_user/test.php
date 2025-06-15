<!DOCTYPE html>
<html lang="en" ⚡>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Sign Up - Shohochor Elderly Care Platform</title>
        <meta name="description" content="Create your Shohochor account to access AI-powered solutions for elderly care.">
        <link href="./signup_user.php" rel="canonical">
        
        <!-- AMP required scripts -->
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
        <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
        <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
        
        <!-- Critical AMP Boilerplate - Required for sidebar to work -->
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
            
            /* Form Styles */
            .signup-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 30px 20px;
            }
            
            .signup-form {
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
            
            .form-row {
                display: flex;
                gap: 15px;
                margin-bottom: 20px;
            }
            
            .form-row .form-group {
                flex: 1;
                margin-bottom: 0;
            }
            
            .checkbox-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .checkbox-container input {
                margin-right: 10px;
            }
            
            .user-type-selector {
                display: flex;
                gap: 15px;
                margin-bottom: 25px;
            }
            
            .user-type-option {
                flex: 1;
                text-align: center;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s;
                position: relative;
            }
            
            .user-type-option:hover {
                border-color: #0096ad;
            }
            
            .user-type-option.selected {
                background-color: rgba(0, 150, 173, 0.1);
                border-color: #0096ad;
            }
            
            .user-type-option svg {
                width: 40px;
                height: 40px;
                margin-bottom: 10px;
                fill: #4e5a60;
            }
            
            .user-type-option.selected svg {
                fill: #0096ad;
            }
            
            .user-type-option h3 {
                font-size: 16px;
                margin: 0;
                color: #4e5a60;
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
            
            .login-link {
                text-align: center;
                margin-top: 20px;
            }
            
            .login-link a {
                color: #0096ad;
                text-decoration: none;
            }
            
            .login-link a:hover {
                text-decoration: underline;
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
                
                /* Form Layout */
                .form-row {
                    flex-direction: column;
                    gap: 20px;
                }
                
                .user-type-selector {
                    flex-direction: column;
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
                        <a href="../login_user/login_user.php" style="background-color: #0096ad; color: white; padding: 8px 15px; border-radius: 4px; display: inline-block; text-decoration: none;">LOGIN</a>
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
                
                <!-- Login Button -->
                <div style="margin-top: 30px; background-color: #ffffff;">
                    <a href="../login_user/login_user.php" style="background-color: #0096ad; color: white; padding: 12px; border-radius: 4px; display: block; text-align: center; text-decoration: none; font-weight: 600; font-size: 16px;">LOGIN</a>
                </div>
            </div>
        </amp-sidebar>
        
        <div class="signup-container">
            <div class="form-heading">
                <h1>Create Your Shohochor Account</h1>
                <p>Join our platform to access innovative elderly care solutions</p>
            </div>
            
            <div class="signup-form">
                <form method="post" action-xhr="process_signup.php" target="_top">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="8">
                            <small>At least 8 characters, including a number and a special character</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                        <small>Format: (123) 456-7890</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>I am a:</label>
                        <div class="user-type-selector" id="user-type-selector">
                            <div class="user-type-option" role="button" tabindex="0" 
                                 [class]="userType == 'senior' ? 'user-type-option selected' : 'user-type-option'"
                                 on="tap:AMP.setState({userType: 'senior'})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <h3>Senior</h3>
                                <input type="radio" name="user_type" value="senior" required style="display:none">
                            </div>
                            <div class="user-type-option" role="button" tabindex="0" 
                                 [class]="userType == 'caregiver' ? 'user-type-option selected' : 'user-type-option'"
                                 on="tap:AMP.setState({userType: 'caregiver'})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-4.66 16.88l5.66-5.65 5.66 5.65c-.48.38-1.64 1.12-2.78 1.12-1.37 0-2.75-1-3.91-2.44-.84 1.08-2.05 2.44-3.91 2.44-1.14 0-2.3-.74-2.78-1.12z"/>
                                </svg>
                                <h3>Caregiver</h3>
                                <input type="radio" name="user_type" value="caregiver" required style="display:none">
                            </div>
                            <div class="user-type-option" role="button" tabindex="0" 
                                 [class]="userType == 'family' ? 'user-type-option selected' : 'user-type-option'"
                                 on="tap:AMP.setState({userType: 'family'})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                                <h3>Family Member</h3>
                                <input type="radio" name="user_type" value="family" required style="display:none">
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a></label>
                    </div>
                    
                    <button type="submit" class="submit-btn">Create Account</button>
                    
                    <div class="login-link">
                        <p>Already have an account? <a href="../login_user/login_user.php">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
        
<?php include '../includes/footer.php'; ?>
    </body>
</html>