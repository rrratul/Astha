<?php

session_start();
require_once '../signup_user/config.php';
require_once '../signup_user/functions.php';

// Require login and caregiver role
requireLogin('caregiver');

// Update last activity
updateLastActivity($_SESSION['user_id']);

// Get user profile
$userProfile = getUserProfile($_SESSION['user_id']);
$welcomeMessage = getWelcomeMessage($_SESSION['user_role'], $_SESSION['user_first_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregiver Dashboard - Shohochor</title>
    <link rel="stylesheet" href="./caregiver_dashboard.css">
</head>
<body>
    <?php include '../includes/dashboard_header.php'; ?>
    
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav class="dashboard-nav">
                <ul>
                    <?php 
                    $menu = getNavigationMenu($_SESSION['user_role']);
                    foreach ($menu as $title => $url): 
                    ?>
                        <li><a href="<?php echo $url; ?>" class="<?php echo ($title === 'Dashboard') ? 'active' : ''; ?>"><?php echo $title; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <?php echo generateBreadcrumb('Dashboard', $_SESSION['user_role']); ?>
            
            <div class="dashboard-header">
                <h1><?php echo $welcomeMessage; ?></h1>
                <p>Manage your clients and care activities</p>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>My Clients</h3>
                    <p>View and manage your assigned clients</p>
                    <a href="./clients.php" class="card-link">View Clients</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>Today's Schedule</h3>
                    <p>Your appointments and visits for today</p>
                    <a href="./schedule.php" class="card-link">View Schedule</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>Care Plans</h3>
                    <p>Manage and update client care plans</p>
                    <a href="./care_plans.php" class="card-link">Manage Care Plans</a>
                </div>
                
                <div class="dashboard-card">
                    <h3>Reports</h3>
                    <p>Submit daily reports and care updates</p>
                    <a href="./reports.php" class="card-link">Submit Reports</a>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3>Today's Tasks</h3>
                <ul>
                    <li>Visit Mrs. Johnson at 10:00 AM</li>
                    <li>Submit care report for Mr. Smith</li>
                    <li>Medication reminder for Mrs. Brown at 2:00 PM</li>
                </ul>
            </div>
        </main>
    </div>
    
    <?php include '../includes/dashboard_footer.php'; ?>
</body>
</html>