<?php
session_start();

// Include required files
require_once '../login_user/functions.php';
require_once '../signup_user/config.php';
require_once './db_connect.php';

// Authentication checks
if (!isLoggedIn() || $_SESSION['user_role'] !== 'healthcare_provider') {
    header("Location: ../login_user/login_user.php?error=unauthorized");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Healthcare Provider';
$user_first_name = $_SESSION['user_first_name'] ?? 'Healthcare Provider';

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Patient ID is required.";
    header("Location: patients.php");
    exit();
}

$patient_id = (int)$_GET['id'];

// Get patient details
$stmt = $pdo->prepare("
    SELECT u.*,
           TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
    FROM users u
    WHERE u.id = :id
");
$stmt->execute(['id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    $_SESSION['error_message'] = "Patient not found.";
    header("Location: patients.php");
    exit();
}

// Get patient's latest health metrics
$stmt = $pdo->prepare("
    SELECT * FROM health_metrics
    WHERE user_id = :user_id
    ORDER BY recorded_at DESC
    LIMIT 1
");
$stmt->execute(['user_id' => $patient_id]);
$latest_metrics = $stmt->fetch(PDO::FETCH_ASSOC);

// Get patient's health metrics history (last 10 records)
$stmt = $pdo->prepare("
    SELECT * FROM health_metrics
    WHERE user_id = :user_id
    ORDER BY recorded_at DESC
    LIMIT 10
");
$stmt->execute(['user_id' => $patient_id]);
$health_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's appointment history
$stmt = $pdo->prepare("
    SELECT * FROM appointments
    WHERE user_id = :user_id AND doctor_name = :doctor_name
    ORDER BY date DESC, time DESC
");
$stmt->execute([
    'user_id' => $patient_id,
    'doctor_name' => $user_name
]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient's prescriptions
$prescriptions = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name, u.last_name
        FROM prescriptions p
        JOIN users u ON p.doctor_id = u.id
        WHERE p.patient_id = :patient_id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute(['patient_id' => $patient_id]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist or other database error
    // This prevents the fatal error from breaking the page
    $prescriptions = [];
    // Optionally log the error for administrators
    error_log("Prescriptions table error: " . $e->getMessage());
}

// Process notes or observations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_note'])) {
        $note_title = trim($_POST['note_title']);
        $note_content = trim($_POST['note_content']);
        
        if (!empty($note_title) && !empty($note_content)) {
            $stmt = $pdo->prepare("
                INSERT INTO patient_notes 
                (patient_id, doctor_id, title, content) 
                VALUES 
                (:patient_id, :doctor_id, :title, :content)
            ");
            
            $success = $stmt->execute([
                'patient_id' => $patient_id,
                'doctor_id' => $user_id,
                'title' => $note_title,
                'content' => $note_content
            ]);
            
            if ($success) {
                $_SESSION['success_message'] = "Note added successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to add note.";
            }
            
            // Refresh page to avoid form resubmission
            header("Location: patient-details.php?id=" . $patient_id);
            exit();
        } else {
            $_SESSION['error_message'] = "Note title and content are required.";
        }
    }
}

// Get patient's notes
$stmt = $pdo->prepare("
    SELECT n.*, u.first_name, u.last_name
    FROM patient_notes n
    JOIN users u ON n.doctor_id = u.id
    WHERE n.patient_id = :patient_id
    ORDER BY n.created_at DESC
");
$stmt->execute(['patient_id' => $patient_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate health status based on latest metrics
$health_status = 'Normal';
$health_status_class = 'badge-success';

if ($latest_metrics) {
    if (
        $latest_metrics['systolic'] > 140 || 
        $latest_metrics['diastolic'] > 90 || 
        $latest_metrics['pulse'] > 100 || 
        $latest_metrics['pulse'] < 60 || 
        $latest_metrics['blood_sugar'] > 180
    ) {
        $health_status = 'Needs Attention';
        $health_status_class = 'badge-danger';
    } else if (
        $latest_metrics['systolic'] > 130 ||
        $latest_metrics['diastolic'] > 85 ||
        $latest_metrics['pulse'] > 90 ||
        $latest_metrics['blood_sugar'] > 140
    ) {
        $health_status = 'Monitor';
        $health_status_class = 'badge-warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></title>
    
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
    
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            color: #0096ad;
            margin-right: 12px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 15px;
        }
        
        .badge-success {
            background-color: #e6ffec;
            color: #22543d;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .patient-overview {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .patient-sidebar {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .patient-profile {
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(135deg, #0096ad 0%, #00b0cc 100%);
            color: #fff;
        }
        
        .patient-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 600;
            color: #0096ad;
            overflow: hidden;
        }
        
        .patient-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .patient-name {
            font-size: 22px;
            margin: 0;
            font-weight: 600;
        }
        
        .patient-info {
            padding: 20px;
        }
        
        .patient-info-item {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        
        .patient-info-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e2f5f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0096ad;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .patient-info-content {
            flex: 1;
        }
        
        .patient-info-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 2px;
        }
        
        .patient-info-value {
            font-weight: 500;
            color: #2d3748;
        }
        
        .patient-tabs {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        .patient-tab {
            padding: 12px 20px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 3px solid transparent;
            cursor: pointer;
        }
        
        .patient-tab.active {
            color: #0096ad;
            border-bottom-color: #0096ad;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
        }
        
        .card-title i {
            margin-right: 10px;
            color: #0096ad;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
        }
        
        .metric-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        
        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e2f5f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0096ad;
            margin: 0 auto 10px;
            font-size: 20px;
        }
        
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .metric-value.critical {
            color: #e53e3e;
        }
        
        .metric-value.warning {
            color: #dd6b20;
        }
        
        .metric-name {
            font-size: 13px;
            color: #718096;
        }
        
        .metric-date {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 5px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            text-align: left;
            padding: 15px 20px;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            background-color: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .data-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .note-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        
        .note-title {
            font-weight: 600;
            color: #2d3748;
        }
        
        .note-meta {
            font-size: 12px;
            color: #718096;
        }
        
        .note-content {
            color: #4a5568;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #0096ad;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 150, 173, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: #0096ad;
            color: #fff;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #007a8c;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .btn-outline:hover {
            background-color: #f7fafc;
        }
        
        .health-chart {
            height: 300px;
            margin-top: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #822727;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 15px;
        }
        
        .empty-state-text {
            color: #718096;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 900px) {
            .patient-overview {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .header-actions {
                justify-content: space-between;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include_once "includes/nav.php"; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-injured"></i>
                    Patient Details
                    <span class="badge <?php echo $health_status_class; ?>"><?php echo $health_status; ?></span>
                </h1>
                
                <div class="header-actions">
                    <a href="patients.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Back to Patients
                    </a>
                    <a href="appointments.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        Schedule Appointment
                    </a>
                    <a href="prescriptions.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                        <i class="fas fa-prescription"></i>
                        Write Prescription
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['success_message']; ?></span>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['error_message']; ?></span>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="patient-overview">
                <div class="patient-sidebar">
                    <div class="patient-profile">
                        <div class="patient-avatar">
                            <?php if (!empty($patient['profile_picture']) && !empty($patient['profile_picture_type'])): ?>
                                <img src="data:<?php echo $patient['profile_picture_type']; ?>;base64,<?php echo base64_encode($patient['profile_picture']); ?>" alt="Patient Avatar">
                            <?php else: ?>
                                <?php echo strtoupper(substr($patient['first_name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <h2 class="patient-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
                        <p class="patient-id">Patient ID: <?php echo $patient['id']; ?></p>
                    </div>
                    
                    <div class="patient-info">
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-birthday-cake"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Date of Birth / Age</div>
                                <div class="patient-info-value">
                                    <?php 
                                        echo isset($patient['date_of_birth']) 
                                            ? date('M d, Y', strtotime($patient['date_of_birth'])) . ' (' . $patient['age'] . ' years)'
                                            : 'Not provided';
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-venus-mars"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Gender</div>
                                <div class="patient-info-value">
                                    <?php echo !empty($patient['gender']) ? ucfirst($patient['gender']) : 'Not specified'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Phone</div>
                                <div class="patient-info-value">
                                    <?php echo !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'Not provided'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Email</div>
                                <div class="patient-info-value">
                                    <?php echo htmlspecialchars($patient['email']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Address</div>
                                <div class="patient-info-value">
                                    <?php echo !empty($patient['address']) ? htmlspecialchars($patient['address']) : 'Not provided'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Joined On</div>
                                <div class="patient-info-value">
                                    <?php echo date('M d, Y', strtotime($patient['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($patient['emergency_contact'])): ?>
                        <div class="patient-info-item">
                            <div class="patient-info-icon">
                                <i class="fas fa-ambulance"></i>
                            </div>
                            <div class="patient-info-content">
                                <div class="patient-info-label">Emergency Contact</div>
                                <div class="patient-info-value">
                                    <?php echo htmlspecialchars($patient['emergency_contact']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="patient-details">
                    <div class="patient-tabs">
                        <div class="patient-tab active" data-tab="health-metrics">Health Metrics</div>
                        <div class="patient-tab" data-tab="appointments">Appointments</div>
                        <div class="patient-tab" data-tab="prescriptions">Prescriptions</div>
                        <div class="patient-tab" data-tab="notes">Notes & Observations</div>
                    </div>
                    
                    <div id="health-metrics" class="tab-content active">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-heartbeat"></i>
                                    Current Health Metrics
                                </h2>
                                <a href="health-records.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-outline">
                                    <i class="fas fa-history"></i>
                                    Full History
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if ($latest_metrics): ?>
                                <div class="metrics-grid">
                                    <?php if (isset($latest_metrics['systolic']) && $latest_metrics['systolic'] > 0): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-heartbeat"></i>
                                        </div>
                                        <div class="metric-value <?php echo ($latest_metrics['systolic'] > 140 || $latest_metrics['diastolic'] > 90) ? 'critical' : (($latest_metrics['systolic'] > 130 || $latest_metrics['diastolic'] > 85) ? 'warning' : ''); ?>">
                                            <?php echo $latest_metrics['systolic']; ?>/<?php echo $latest_metrics['diastolic']; ?>
                                        </div>
                                        <div class="metric-name">Blood Pressure (mmHg)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($latest_metrics['pulse']) && $latest_metrics['pulse'] > 0): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                        <div class="metric-value <?php echo ($latest_metrics['pulse'] > 100 || $latest_metrics['pulse'] < 60) ? 'critical' : (($latest_metrics['pulse'] > 90) ? 'warning' : ''); ?>">
                                            <?php echo $latest_metrics['pulse']; ?>
                                        </div>
                                        <div class="metric-name">Pulse Rate (bpm)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($latest_metrics['blood_sugar'])): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-tint"></i>
                                        </div>
                                        <div class="metric-value <?php echo ($latest_metrics['blood_sugar'] > 180) ? 'critical' : (($latest_metrics['blood_sugar'] > 140) ? 'warning' : ''); ?>">
                                            <?php echo $latest_metrics['blood_sugar']; ?>
                                        </div>
                                        <div class="metric-name">Blood Sugar (mg/dL)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($latest_metrics['temperature'])): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-thermometer-half"></i>
                                        </div>
                                        <div class="metric-value <?php echo ($latest_metrics['temperature'] > 38) ? 'critical' : (($latest_metrics['temperature'] > 37.5) ? 'warning' : ''); ?>">
                                            <?php echo $latest_metrics['temperature']; ?>
                                        </div>
                                        <div class="metric-name">Temperature (°C)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($latest_metrics['oxygen_level'])): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-lungs"></i>
                                        </div>
                                        <div class="metric-value <?php echo ($latest_metrics['oxygen_level'] < 95) ? 'critical' : (($latest_metrics['oxygen_level'] < 97) ? 'warning' : ''); ?>">
                                            <?php echo $latest_metrics['oxygen_level']; ?>%
                                        </div>
                                        <div class="metric-name">Oxygen Level (SpO2)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($latest_metrics['weight'])): ?>
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-weight"></i>
                                        </div>
                                        <div class="metric-value">
                                            <?php echo $latest_metrics['weight']; ?>
                                        </div>
                                        <div class="metric-name">Weight (kg)</div>
                                        <div class="metric-date"><?php echo date('M d, Y', strtotime($latest_metrics['recorded_at'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <p class="empty-state-text">No health metrics available for this patient.</p>
                                    <a href="health-records.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">Add Health Metrics</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-chart-line"></i>
                                    Health History
                                </h2>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($health_history)): ?>
                                <div class="table-container">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Blood Pressure</th>
                                                <th>Pulse</th>
                                                <th>Blood Sugar</th>
                                                <th>Temperature</th>
                                                <th>Oxygen Level</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($health_history as $record): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($record['recorded_at'])); ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($record['systolic']) && $record['systolic'] > 0) {
                                                        echo "<span class='" . (($record['systolic'] > 140 || $record['diastolic'] > 90) ? 'critical' : '') . "'>";
                                                        echo $record['systolic'] . '/' . $record['diastolic'] . ' mmHg';
                                                        echo "</span>";
                                                    } else {
                                                        echo "Not recorded";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (isset($record['pulse']) && $record['pulse'] > 0) {
                                                        echo "<span class='" . (($record['pulse'] > 100 || $record['pulse'] < 60) ? 'critical' : '') . "'>";
                                                        echo $record['pulse'] . ' bpm';
                                                        echo "</span>";
                                                    } else {
                                                        echo "Not recorded";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($record['blood_sugar'])) {
                                                        echo "<span class='" . ($record['blood_sugar'] > 180 ? 'critical' : '') . "'>";
                                                        echo $record['blood_sugar'] . ' mg/dL';
                                                        echo "</span>";
                                                    } else {
                                                        echo "Not recorded";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($record['temperature'])) {
                                                        echo "<span class='" . ($record['temperature'] > 38 ? 'critical' : '') . "'>";
                                                        echo $record['temperature'] . ' °C';
                                                        echo "</span>";
                                                    } else {
                                                        echo "Not recorded";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($record['oxygen_level'])) {
                                                        echo "<span class='" . ($record['oxygen_level'] < 95 ? 'critical' : '') . "'>";
                                                        echo $record['oxygen_level'] . '%';
                                                        echo "</span>";
                                                    } else {
                                                        echo "Not recorded";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <p class="empty-state-text">No health history available for this patient.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div id="appointments" class="tab-content">
                        <!-- Appointments content -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-calendar-check"></i>
                                    Appointment History
                                </h2>
                                <a href="appointments.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Schedule New
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($appointments)): ?>
                                <div class="table-container">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($appointment['date'])); ?> at 
                                                    <?php echo date('h:i A', strtotime($appointment['time'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($appointment['title']); ?></td>
                                                <td>
                                                    <?php 
                                                        $status_class = '';
                                                        switch ($appointment['status']) {
                                                            case 'scheduled':
                                                                $status_class = 'badge-primary';
                                                                break;
                                                            case 'completed':
                                                                $status_class = 'badge-success';
                                                                break;
                                                            case 'cancelled':
                                                                $status_class = 'badge-danger';
                                                                break;
                                                            default:
                                                                $status_class = 'badge-warning';
                                                        }
                                                    ?>
                                                    <span class="status-badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="appointment-details.php?id=<?php echo $appointment['id']; ?>" class="btn-icon" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($appointment['status'] === 'scheduled'): ?>
                                                            <form method="post" action="appointments.php" style="display:inline;">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                                <button type="submit" name="complete_appointment" class="btn-icon" title="Mark as Completed">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <form method="post" action="appointments.php" style="display:inline;">
                                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                                <button type="submit" name="cancel_appointment" class="btn-icon" title="Cancel Appointment" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ($appointment['status'] === 'completed'): ?>
                                                            <a href="prescriptions.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn-icon" title="Write Prescription">
                                                                <i class="fas fa-prescription"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <p class="empty-state-text">No appointments found for this patient.</p>
                                    <a href="appointments.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">Schedule Appointment</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div id="prescriptions" class="tab-content">
                        <!-- Prescriptions content -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                    Prescriptions
                                </h2>
                                <a href="prescriptions.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Write New Prescription
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($prescriptions)): ?>
                                <div class="table-container">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Diagnosis</th>
                                                <th>Doctor</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($prescriptions as $prescription): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($prescription['diagnosis']); ?></td>
                                                <td>Dr. <?php echo htmlspecialchars($prescription['first_name'] . ' ' . $prescription['last_name']); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="prescription-details.php?id=<?php echo $prescription['id']; ?>" class="btn-icon" title="View Prescription">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="prescription-print.php?id=<?php echo $prescription['id']; ?>" class="btn-icon" target="_blank" title="Print Prescription">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <?php if ($prescription['doctor_id'] == $user_id): ?>
                                                        <a href="prescriptions.php?edit=<?php echo $prescription['id']; ?>" class="btn-icon" title="Edit Prescription">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-prescription"></i>
                                    </div>
                                    <p class="empty-state-text">No prescriptions found for this patient.</p>
                                    <a href="prescriptions.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">Write Prescription</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div id="notes" class="tab-content">
                        <!-- Notes content -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-clipboard"></i>
                                    Clinical Notes
                                </h2>
                            </div>
                            <div class="card-body">
                                <form method="post" action="patient-details.php?id=<?php echo $patient_id; ?>">
                                    <div class="form-group">
                                        <label class="form-label" for="note_title">Note Title</label>
                                        <input type="text" id="note_title" name="note_title" class="form-control" placeholder="e.g., Initial Assessment, Follow-up, Treatment Plan" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="note_content">Note Content</label>
                                        <textarea id="note_content" name="note_content" class="form-control" placeholder="Enter your clinical observations, assessments, recommendations, etc." required></textarea>
                                    </div>
                                    <button type="submit" name="add_note" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Note
                                    </button>
                                </form>
                                
                                <hr style="margin: 30px 0;">
                                
                                <h3 style="font-size: 18px; margin-bottom: 20px;">Previous Notes</h3>
                                
                                <?php if (!empty($notes)): ?>
                                    <?php foreach ($notes as $note): ?>
                                    <div class="note-card">
                                        <div class="note-header">
                                            <div class="note-title"><?php echo htmlspecialchars($note['title']); ?></div>
                                            <div class="note-meta">
                                                By Dr. <?php echo htmlspecialchars($note['first_name'] . ' ' . $note['last_name']); ?> on 
                                                <?php echo date('M d, Y \a\t h:i A', strtotime($note['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="note-content">
                                            <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 20px 0;">
                                        <p class="empty-state-text">No notes have been added yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once "../elderly/includes/footer.php"; ?>
    <?php include_once "includes/mobile_nav.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.patient-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.tab;
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    tab.classList.add('active');
                    document.getElementById(target).classList.add('active');
                });
            });
            
            // Get tab from URL hash if present
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                const tab = document.querySelector(`.patient-tab[data-tab="${hash}"]`);
                if (tab) {
                    tab.click();
                }
            }
        });
    </script>
</body>
</html>