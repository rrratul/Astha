<!-- filepath: c:\xampp\htdocs\Shohochor\includes\nav.php -->
<header role="banner" class="ui-header">
    <div class="ui-header-logo">
        <a role="link" aria-label="Home" href="./index.php">
            <h1><span class="sho">Sho</span><span class="chor">hochor</span></h1>
            <style>
                .sho {
                    color: orange;
                    font: 3em sans-serif;
                }
                
                .chor {
                    color: #0096ad;
                    font: 3em sans-serif;
                }
            </style>
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
                <a href="../login_user/login_user.php" style="background-color: #0096ad; color: white; padding: 8px 15px; border-radius: 4px; display: inline-block; text-decoration: none;">LOGIN</a>
            </li>
        </ul>
        <button on="tap:sidebar1.toggle" tabindex="0" class="ui-header-ham hamburger hamburger--collapse" type="button">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>
    </div>
</header>