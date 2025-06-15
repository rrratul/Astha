<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shohochor - Professional Senior Care Services">
    <title>Shohochor - Senior Care Services</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Full-width navbar with proper spacing */
        body {
            margin: 0;
            padding: 0;
        }
        
        .ui-header {
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .ui-header-inner {
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
            min-width: 180px !important;
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
        
        .ui-header-navbar-dropdown {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 30px !important;
        }
    </style>
</head>
<body>
    <header role="banner" class="ui-header">
        <div class="ui-header-inner">
            <div class="ui-header-logo">
                <a role="link" aria-label="Home" href="../index.php">
                    <h1><span class="sho">Sho</span><span class="chor">hochor</span></h1>
                </a>
            </div>
            <div class="ui-header-navbar">
                <ul class="ui-header-navbar-dropdown">
                    <li class="navbar-dropdown-item-1">
                        <a class="ui-textlink-families" href="#services">SENIOR CARE SERVICES</a>
                    </li>
                    <li class="navbar-dropdown-item-2">
                        <a class="ui-textlink-jobs" href="#features">PLATFORM FEATURES</a>
                    </li>
                    <li class="navbar-dropdown-item-3">
                        <a class="ui-textlink-care" href="#contact">CONTACT US</a>
                    </li>
                    <li class="navbar-dropdown-item-4">
                        <a class="ui-textlink-aboutus" href="#about">ABOUT US</a>
                    </li>
                    <li class="navbar-dropdown-item-5">
                        <a class="nav-link login-btn" href="login_user\login_user.php">LOGIN</a>
                    </li>
                    <li class="navbar-dropdown-item-6">
                        <a class="nav-link signup-btn" href="signup_user\signup_user.php">SIGN UP</a>
                    </li>
                </ul>
                <button on="tap:sidebar1.toggle" tabindex="0" class="ui-header-ham hamburger hamburger--collapse" type="button" role="button" aria-label="Menu">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
            </div>
        </header>
    <style>
        /* Enhanced Header Styling */
        .ui-header {
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #0096ad;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0;
        }
        
        .ui-header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1400px;
            padding: 0 30px;
        }

        .ui-header-logo {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            height: 100%;
            min-width: 180px;
            overflow: visible;
        }

        .ui-header-logo a {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0;
            margin: 0;
            white-space: nowrap;
            overflow: visible;
        }
        
        .ui-header-logo h1 {
            margin: 0;
            line-height: 1;
            white-space: nowrap;
            overflow: visible;
            min-width: 180px;
        }
        
        .sho {
            color: #ff9500;
            font-size: 32px;
            font-weight: 800;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
            display: inline-block;
            white-space: nowrap;
        }

        .chor {
            color: #0096ad;
            font-size: 32px;
            font-weight: 800;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
            display: inline-block;
            white-space: nowrap;
        }

        /* Main navigation styling */
        .ui-header-navbar {
            display: flex;
            align-items: center;
        }
        
        .ui-header-navbar-dropdown {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 30px;
            align-items: center;
        }

        .ui-header-navbar-dropdown li a {
            font-size: 13px;
            font-weight: 700;
            padding: 10px 4px;
            text-decoration: none;
            color: #444;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            position: relative;
        }

        .ui-header-navbar-dropdown li a:hover {
            color: #0096ad;
        }
        
        .ui-header-navbar-dropdown li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #0096ad;
            transition: width 0.3s ease;
        }
        
        .ui-header-navbar-dropdown li a:hover::after {
            width: 100%;
        }

        /* Login/Signup button styling */
        .nav-link.login-btn, .nav-link.signup-btn {
            font-size: 13px;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,150,173,0.1);
            letter-spacing: 0.5px;
            margin-left: 15px;
        }
        
        .nav-link.login-btn {
            background: #fff;
            color: #0096ad;
            border: 2px solid #0096ad;
        }
        
        .nav-link.signup-btn {
            background: #0096ad;
            color: #fff;
            border: 2px solid #0096ad;
        }
        
        .nav-link.login-btn:hover {
            background: rgba(0,150,173,0.05);
            box-shadow: 0 4px 12px rgba(0,150,173,0.15);
            transform: translateY(-2px);
        }
        
        .nav-link.signup-btn:hover {
            background: #007a8a;
            box-shadow: 0 4px 12px rgba(0,150,173,0.25);
            transform: translateY(-2px);
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 1200px) {
            .ui-header-navbar-dropdown {
                gap: 20px;
            }
            
            .ui-header-navbar-dropdown li a {
                font-size: 12px;
                padding: 8px 2px;
            }
        }
        
        @media (max-width: 992px) {
            .ui-header-navbar-dropdown {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }
            
            .ui-header {
                height: 70px;
            }
            
            .ui-header-inner {
                padding: 0 20px;
            }
            
            .sho, .chor {
                font-size: 28px;
            }
        }
        
        @media (max-width: 576px) {
            .ui-header {
                height: 60px;
            }
            
            .ui-header-inner {
                padding: 0 15px;
            }
            
            .sho, .chor {
                font-size: 24px;
            }
        }
    </style>

    <!-- Smooth scrolling script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all the navigation links
            const navLinks = document.querySelectorAll('.ui-header-navbar-dropdown a[href^="#"]');
            
            // Add click event to each link
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Prevent default behavior
                    e.preventDefault();
                    
                    // Get the target section ID from the href
                    const targetId = this.getAttribute('href');
                    
                    // Get the target element
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        // Calculate header height for offset
                        const headerHeight = document.querySelector('.ui-header').offsetHeight;
                        
                        // Get the position of the target element
                        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                        
                        // Scroll smoothly to the target position
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>