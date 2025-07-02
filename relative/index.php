<?php
session_start();
require_once '../../Astha/includes/config.php';
require_once './functions.php';
// Authentication checks
if (!isLoggedIn()) {
    header("Location: ../login_user/login_user.php");
    exit();
}

// Role checking
if ($_SESSION['user_role'] !== 'family_member') {
    header("Location: ../logout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$elderly_relatives = get_elderly_relatives($user_id);

// Fetch data for display
$emergency_alerts = get_emergency_alerts($user_id);
$community_events = get_community_events();
$scheduled_calls = get_scheduled_calls($user_id);
$life_stories = get_life_stories($user_id);

// Get notifications count
$alert_count = $emergency_alerts->num_rows;

// Determine greeting based on time of day
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

// Get user's first name
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Member Dashboard - Astha</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon/favicon-16x16.png">
    <link rel="mask-icon" href="../assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
   <?php include_once '../../Astha/relative/includes/header.php'; ?>

<!-- Mobile Navigation -->
<?php include_once '../../Astha/relative/includes/mobile_nav.php'; ?>
    
    <main>
        <!-- Alert Banner -->
        <?php if($alert_count > 0): ?>
        <div class="alert-banner">
            <div class="alert-content">
                <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
                <span class="alert-message"><?php echo $alert_count; ?> Emergency Alert<?php echo $alert_count > 1 ? 's' : ''; ?> - Immediate Attention Required</span>
            </div>
            <a href="#emergencySection" class="alert-action">View Alerts</a>
        </div>
        <?php endif; ?>
        
        <!-- Dashboard Welcome -->
        <div class="dashboard-welcome">
            <div class="welcome-text">
                <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($user_first_name); ?>!</h1>
                <p>Welcome to your family member dashboard. Stay connected with your elderly loved ones and monitor their wellbeing.</p>
            </div>
            <div class="welcome-actions">
                <a href="#" class="welcome-button" data-bs-toggle="modal" data-bs-target="#videoCallModal">
                    <i class="fas fa-video"></i>
                    Start Video Call
                </a>
                <a href="activity-monitor.php" class="welcome-button">
                    <i class="fas fa-chart-line"></i>
                    Activity Reports
                </a>
                <a href="manage_connections.php" class="welcome-button">
                    <i class="fas fa-users"></i>
                    Manage Connections
                </a>
            </div>
        </div>
        
        <!-- Relative Selector -->
        <?php if(count($elderly_relatives) > 0): ?>
        <div class="relative-selector">
            <div class="selector-group">
                <label for="relativeSelector" class="selector-label">Select a relative to focus on:</label>
                <select class="form-select" id="relativeSelector">
                    <option value="all">All Relatives</option>
                    <?php foreach($elderly_relatives as $relative): ?>
                    <option value="<?php echo $relative['id']; ?>">
                        <?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="action-group">
                <div class="communication-actions">
                    <a href="#" class="action-btn video-btn" data-bs-toggle="modal" data-bs-target="#videoCallModal">
                        <i class="fas fa-video"></i> Video Call
                    </a>
                    <a href="#" class="action-btn message-btn" data-bs-toggle="modal" data-bs-target="#messageModal">
                        <i class="fas fa-comment-dots"></i> Message
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="notification-card">
            <div class="notification-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="notification-content">
                <h3 class="notification-title">No Connected Relatives</h3>
                <p class="notification-message">You don't have any elderly relatives connected yet. Please contact support to establish connections.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Connected Relatives</h3>
                <p><?php echo count($elderly_relatives); ?></p>
                <div class="stat-change">
                    <i class="fas fa-user-friends"></i>
                    <span>Family members</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Active Alerts</h3>
                <p><?php echo $alert_count; ?></p>
                <div class="stat-change <?php echo $alert_count > 0 ? 'stat-decrease' : 'stat-increase'; ?>">
                    <i class="fas <?php echo $alert_count > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                    <span><?php echo $alert_count > 0 ? 'Requires attention' : 'All clear'; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Scheduled Calls</h3>
                <p><?php echo $scheduled_calls->num_rows; ?></p>
                <div class="stat-change">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Upcoming</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Community Events</h3>
                <p><?php echo $community_events->num_rows; ?></p>
                <div class="stat-change">
                    <i class="fas fa-users"></i>
                    <span>Available</span>
                </div>
            </div>
        </div>
        
        <!-- Rest of your content organized in feature cards -->
        <div class="row">
            <!-- Your existing content sections -->
            <!-- ... -->
        </div>
        
        <!-- Add this section to the relative dashboard -->
        <div class="container my-5">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>My Connected Relatives</h2>
                        <a href="add_relative.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Add New Relative
                        </a>
                    </div>
                    
                    <?php if (count($elderly_relatives) > 0): ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($elderly_relatives as $relative): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="position-relative">
                                        <div class="card-img-top relative-banner d-flex align-items-center justify-content-center" style="height: 100px; background-color: #0096ad;">
                                            <?php if (!empty($relative['profile_picture'])): ?>
                                                <img src="../uploads/profiles/<?php echo $relative['profile_picture']; ?>" 
                                                    class="relative-profile-img" alt="<?php echo htmlspecialchars($relative['first_name']); ?>">
                                            <?php else: ?>
                                                <div class="relative-profile-initial">
                                                    <?php echo strtoupper(substr($relative['first_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body pt-5 mt-4">
                                        <h3 class="card-title text-center mb-3">
                                            <?php echo htmlspecialchars($relative['first_name'] . ' ' . $relative['last_name']); ?>
                                        </h3>
                                        <p class="text-center text-muted mb-3">Your <?php echo htmlspecialchars($relative['relationship']); ?></p>
                                        
                                        <div class="card-details">
                                            <?php /* Remove or comment out this section
                                            <?php if (!empty($relative['age'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-birthday-cake"></i>
                                                <span><?php echo htmlspecialchars($relative['age']); ?> years old</span>
                                            </div>
                                            <?php endif; ?>
                                            */ ?>
                                            
                                            <?php if (!empty($relative['email'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-envelope"></i>
                                                <span><?php echo htmlspecialchars($relative['email']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($relative['phone'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($relative['phone']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($relative['address'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($relative['address']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top-0">
                                        <div class="d-flex justify-content-between">
                                            <a href="relative_detail.php?id=<?php echo $relative['id']; ?>" class="btn btn-outline-primary w-100 me-2">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                            <a href="#" class="btn btn-primary w-100 ms-2" data-bs-toggle="modal" data-bs-target="#videoCallModal" data-name="<?php echo htmlspecialchars($relative['first_name']); ?>" data-id="<?php echo $relative['id']; ?>">
                                                <i class="fas fa-video me-2"></i>Call Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                                <h3>No Connected Relatives</h3>
                                <p class="text-muted">You don't have any connected elderly relatives yet. Add a relative to get started.</p>
                                <a href="add_relative.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus-circle me-2"></i>Add Relative
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Video Call Modal -->
    <div class="modal fade" id="videoCallModal" tabindex="-1" aria-labelledby="videoCallModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoCallModalLabel">Video Call</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-video fa-4x text-primary"></i>
                    </div>
                    <h3>Starting Video Call</h3>
                    <p class="text-muted mb-4">Connecting to <span id="callRecipientName">your relative</span>...</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-4"><small>This is a demo. In a real app, this would initiate a video call.</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script>
        // Your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Handle video call modal
            const videoCallModal = document.getElementById('videoCallModal');
            if (videoCallModal) {
                videoCallModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const name = button.getAttribute('data-name');
                    const recipientElement = document.getElementById('callRecipientName');
                    if (recipientElement && name) {
                        recipientElement.textContent = name;
                    }
                });
            }
        });
    </script>
</body>
</html>