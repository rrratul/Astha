<?php
session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';
require_once 'db_connect.php'; // Add this line to include the database connection

// Authentication checks
if (!isLoggedIn()) {
    header("Location: ../login_user/login_user.php");
    exit();
}

// Role checking
if ($_SESSION['user_role'] !== 'healthcare_provider') {
    header("Location: ../logout.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Healthcare Provider';
$user_first_name = $_SESSION['user_first_name'] ?? 'Healthcare Provider';

// Now $pdo is defined, so these queries will work
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as patient_count FROM appointments WHERE status != 'cancelled' AND doctor_name = :doctor_name");
$stmt->execute(['doctor_name' => $user_name]);
$patient_count = $stmt->fetch(PDO::FETCH_ASSOC)['patient_count'] ?? 0;

// Get upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.first_name, u.last_name, u.profile_picture 
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.doctor_name = :doctor_name 
    AND a.date >= CURDATE()
    AND a.status = 'scheduled'
    ORDER BY a.date ASC, a.time ASC
    LIMIT 5
");
$stmt->execute(['doctor_name' => $user_name]);
$upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent health metrics
$stmt = $pdo->prepare("
    SELECT hm.*, u.first_name, u.last_name
    FROM health_metrics hm
    JOIN users u ON hm.user_id = u.id
    JOIN appointments a ON a.user_id = u.id
    WHERE a.doctor_name = :doctor_name
    GROUP BY hm.user_id
    ORDER BY hm.recorded_at DESC
    LIMIT 5
");
$stmt->execute(['doctor_name' => $user_name]);
$recent_metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get time of day greeting
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Provider Dashboard - Shohochor</title>
    
    <!-- Google Translate -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            // Initialize desktop translate
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
            
            // Initialize mobile translate separately
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'mobile-google-translate');
        }
    </script>
    
    <!-- Styles and fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../elderly/includes/style.css">
    <link rel="stylesheet" href="./css/healthcare.css">
     <link rel="stylesheet" href="./includes/style.css">
    <style>
        /* Healthcare Provider Dashboard Specific Styles */
        .healthcare-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-welcome {
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-text h1 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .welcome-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
            max-width: 600px;
        }
        
        .welcome-actions {
            display: flex;
            gap: 15px;
        }
        
        .welcome-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .welcome-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #4a5568;
            font-size: 16px;
            font-weight: 600;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .stat-card .stat-change {
            font-size: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        
        .stat-increase {
            color: #48bb78;
        }
        
        .stat-decrease {
            color: #e53e3e;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            color: #0096ad;
        }
        
        .section-action {
            color: #0096ad;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        .section-action:hover {
            color: #007a8c;
            text-decoration: underline;
        }
        
        .appointment-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .appointment-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 10px;
            background: #f8fafc;
            transition: transform 0.2s;
        }
        
        .appointment-item:hover {
            transform: translateX(5px);
            background: #f0fafc;
        }
        
        .appointment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            color: #4a5568;
            overflow: hidden;
        }
        
        .appointment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .appointment-info {
            flex: 1;
        }
        
        .appointment-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .appointment-details {
            font-size: 14px;
            color: #4a5568;
        }
        
        .appointment-time {
            font-weight: 600;
            color: #0096ad;
        }
        
        .appointment-actions {
            display: flex;
            gap: 10px;
        }
        
        .appointment-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .appointment-action-btn:hover {
            background: #0096ad;
            color: white;
        }
        
        .alert-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .alert-item {
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid;
            background: #f8fafc;
        }
        
        .alert-item.urgent {
            border-left-color: #e53e3e;
        }
        
        .alert-item.warning {
            border-left-color: #ed8936;
        }
        
        .alert-item.info {
            border-left-color: #3182ce;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2d3748;
        }
        
        .alert-description {
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .alert-meta {
            font-size: 12px;
            color: #718096;
            display: flex;
            justify-content: space-between;
        }
        
        .patient-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .patient-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid #e2e8f0;
            color: #4a5568;
            font-weight: 600;
        }
        
        .patient-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .patient-table tr:hover {
            background-color: #f8fafc;
        }
        
        .patient-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .patient-mini-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #4a5568;
            overflow: hidden;
        }
        
        .patient-mini-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .metric-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .metric-normal {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .metric-warning {
            background-color: #fefcbf;
            color: #744210;
        }
        
        .metric-danger {
            background-color: #fed7d7;
            color: #822727;
        }
        
        .patient-action {
            color: #0096ad;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .patient-action:hover {
            color: #007a8c;
            text-decoration: underline;
        }
        
        @media (max-width: 992px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .welcome-actions {
                display: none;
            }
            
            .appointment-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .patient-table {
                font-size: 14px;
            }
            
            .patient-table td, .patient-table th {
                padding: 10px;
            }
            
            .metric-badge {
                padding: 3px 6px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Move navigation to top of body - BEFORE main content -->
    <?php include_once "includes/nav.php"; ?>
    
    <!-- Main content -->
    <main class="main-content">
        <div class="healthcare-dashboard">
            <div class="dashboard-welcome">
                <div class="welcome-text">
                    <h1><?php echo $greeting; ?>, Dr. <?php echo htmlspecialchars($user_first_name); ?>!</h1>
                    <p>Welcome to your healthcare provider dashboard. You have <?php echo $upcoming_appointments ? count($upcoming_appointments) : 'no'; ?> upcoming appointments today.</p>
                </div>
                <div class="welcome-actions">
                    <button class="welcome-button">
                        <i class="fas fa-calendar-plus"></i>
                        Add Appointment
                    </button>
                    <button class="welcome-button">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </button>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Patients</h3>
                    <p><?php echo $patient_count; ?></p>
                    <div class="stat-change stat-increase">
                        <i class="fas fa-arrow-up"></i>
                        <span>4% from last month</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Appointments Today</h3>
                    <p><?php 
                        $today_count = 0;
                        foreach ($upcoming_appointments as $appointment) {
                            if (date('Y-m-d', strtotime($appointment['date'])) == date('Y-m-d')) {
                                $today_count++;
                            }
                        }
                        echo $today_count;
                    ?></p>
                    <div class="stat-change">
                        <span><?php echo count($upcoming_appointments); ?> upcoming</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Health Alerts</h3>
                    <p>3</p>
                    <div class="stat-change stat-decrease">
                        <i class="fas fa-arrow-down"></i>
                        <span>2 resolved today</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Task Completion</h3>
                    <p>87%</p>
                    <div class="stat-change stat-increase">
                        <i class="fas fa-arrow-up"></i>
                        <span>5% from last week</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-calendar-check"></i>
                            Upcoming Appointments
                        </h2>
                        <a href="./appointments.php" class="section-action">
                            View All
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="appointment-list">
                        <?php if ($upcoming_appointments): ?>
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <div class="appointment-item">
                                    <div class="appointment-avatar">
                                        <?php if ($appointment['profile_picture']): ?>
                                            <img src="data:<?php echo $appointment['profile_picture_type']; ?>;base64,<?php echo base64_encode($appointment['profile_picture']); ?>" alt="Patient">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="appointment-info">
                                        <div class="appointment-name"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                                        <div class="appointment-details">
                                            <span class="appointment-time">
                                                <?php echo date('d M Y', strtotime($appointment['date'])); ?> at 
                                                <?php echo date('h:i A', strtotime($appointment['time'])); ?>
                                            </span>
                                            • <?php echo htmlspecialchars($appointment['appointment_type']); ?> • 
                                            <?php echo $appointment['is_video_call'] ? 'Video Call' : 'In-Person'; ?>
                                        </div>
                                    </div>
                                    <div class="appointment-actions">
                                        <button class="appointment-action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="appointment-action-btn" title="Start Call">
                                            <i class="fas fa-video"></i>
                                        </button>
                                        <button class="appointment-action-btn" title="Reschedule">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No upcoming appointments scheduled. Add one using the button above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-exclamation-circle"></i>
                            Patient Alerts
                        </h2>
                        <a href="./alerts.php" class="section-action">
                            View All
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="alert-list">
                        <div class="alert-item urgent">
                            <div class="alert-title">Abnormal Blood Pressure</div>
                            <div class="alert-description">Fatima Rahman's systolic pressure has been above 160 for the past 3 readings.</div>
                            <div class="alert-meta">
                                <span>Patient ID: FR-5720</span>
                                <span>1 hour ago</span>
                            </div>
                        </div>
                        <div class="alert-item warning">
                            <div class="alert-title">Medication Compliance Issue</div>
                            <div class="alert-description">Abdul Karim has missed his heart medication doses 3 times this week.</div>
                            <div class="alert-meta">
                                <span>Patient ID: AK-3281</span>
                                <span>4 hours ago</span>
                            </div>
                        </div>
                        <div class="alert-item info">
                            <div class="alert-title">Health Improvement</div>
                            <div class="alert-description">Rahima Begum's glucose levels have normalized after diet changes.</div>
                            <div class="alert-meta">
                                <span>Patient ID: RB-7145</span>
                                <span>Yesterday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-heartbeat"></i>
                        Recent Patient Health Metrics
                    </h2>
                    <a href="./health-metrics.php" class="section-action">
                        View All Metrics
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <table class="patient-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Blood Pressure</th>
                            <th>Heart Rate</th>
                            <th>Blood Sugar</th>
                            <th>Recorded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_metrics): ?>
                            <?php foreach ($recent_metrics as $metric): ?>
                                <tr>
                                    <td>
                                        <div class="patient-name-cell">
                                            <div class="patient-mini-avatar">
                                                <?php echo strtoupper(substr($metric['first_name'], 0, 1)); ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($metric['first_name'] . ' ' . $metric['last_name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($metric['systolic'] && $metric['diastolic']): ?>
                                            <?php 
                                                $bp_class = 'metric-normal';
                                                if ($metric['systolic'] >= 140 || $metric['diastolic'] >= 90) {
                                                    $bp_class = 'metric-danger';
                                                } elseif ($metric['systolic'] >= 130 || $metric['diastolic'] >= 80) {
                                                    $bp_class = 'metric-warning';
                                                }
                                            ?>
                                            <span class="metric-badge <?php echo $bp_class; ?>">
                                                <?php echo $metric['systolic']; ?>/<?php echo $metric['diastolic']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($metric['pulse']): ?>
                                            <?php 
                                                $hr_class = 'metric-normal';
                                                if ($metric['pulse'] >= 100 || $metric['pulse'] <= 50) {
                                                    $hr_class = 'metric-danger';
                                                } elseif ($metric['pulse'] >= 90 || $metric['pulse'] <= 55) {
                                                    $hr_class = 'metric-warning';
                                                }
                                            ?>
                                            <span class="metric-badge <?php echo $hr_class; ?>">
                                                <?php echo $metric['pulse']; ?> BPM
                                            </span>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($metric['blood_sugar']): ?>
                                            <?php 
                                                $bs_class = 'metric-normal';
                                                if ($metric['blood_sugar'] >= 180 || $metric['blood_sugar'] <= 70) {
                                                    $bs_class = 'metric-danger';
                                                } elseif ($metric['blood_sugar'] >= 140 || $metric['blood_sugar'] <= 80) {
                                                    $bs_class = 'metric-warning';
                                                }
                                            ?>
                                            <span class="metric-badge <?php echo $bs_class; ?>">
                                                <?php echo $metric['blood_sugar']; ?> mg/dL
                                            </span>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M, h:i A', strtotime($metric['recorded_at'])); ?></td>
                                    <td>
                                        <a href="./patient.php?id=<?php echo $metric['user_id']; ?>" class="patient-action">View Patient</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No recent health metrics recorded</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Include footer -->
    <?php include_once "../elderly/includes/footer.php"; ?>
    
    <!-- Include mobile navigation - Keep at bottom of page -->
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize accessibility features
            const highContrastBtn = document.getElementById('highContrastBtn');
            const largeTextBtn = document.getElementById('largeTextBtn');
            
            if (highContrastBtn && largeTextBtn) {
                function toggleHighContrast() {
                    document.body.classList.toggle('high-contrast');
                    highContrastBtn.classList.toggle('active');
                    localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
                    
                    if (document.body.classList.contains('high-contrast')) {
                        document.body.classList.add('high-contrast');
                        document.body.style.backgroundColor = "#000";
                        document.body.style.color = "#FFF";
                        
                        // Change background color of various elements
                        document.querySelectorAll('.dashboard-section, .stat-card').forEach(function(element) {
                            element.style.backgroundColor = "#222";
                            element.style.color = "#FFF";
                            element.style.border = "1px solid #444";
                        });
                        
                        showToast("High contrast mode enabled");
                    } else {
                        document.body.classList.remove('high-contrast');
                        document.body.style.backgroundColor = "";
                        document.body.style.color = "";
                        
                        // Reset background color of various elements
                        document.querySelectorAll('.dashboard-section, .stat-card').forEach(function(element) {
                            element.style.backgroundColor = "";
                            element.style.color = "";
                            element.style.border = "";
                        });
                        
                        showToast("High contrast mode disabled");
                    }
                }
                
                function toggleLargeText() {
                    document.body.classList.toggle('large-text');
                    largeTextBtn.classList.toggle('active');
                    localStorage.setItem('largeText', document.body.classList.contains('large-text'));
                    
                    if (document.body.classList.contains('large-text')) {
                        document.body.style.fontSize = "120%";
                        showToast("Large text mode enabled");
                    } else {
                        document.body.style.fontSize = "";
                        showToast("Large text mode disabled");
                    }
                }
                
                // Load user preferences
                if (localStorage.getItem('highContrast') === 'true') {
                    document.body.classList.add('high-contrast');
                    highContrastBtn.classList.add('active');
                    document.body.style.backgroundColor = "#000";
                    document.body.style.color = "#FFF";
                    
                    document.querySelectorAll('.dashboard-section, .stat-card').forEach(function(element) {
                        element.style.backgroundColor = "#222";
                        element.style.color = "#FFF";
                        element.style.border = "1px solid #444";
                    });
                }
                
                if (localStorage.getItem('largeText') === 'true') {
                    document.body.classList.add('large-text');
                    largeTextBtn.classList.add('active');
                    document.body.style.fontSize = "120%";
                }
                
                highContrastBtn.addEventListener('click', toggleHighContrast);
                largeTextBtn.addEventListener('click', toggleLargeText);
            }
            
            // Show toast function
            window.showToast = function(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = 'toast show ' + type;
                toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            };
        });
    </script>
</body>
</html>